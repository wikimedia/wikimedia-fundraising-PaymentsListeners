<?php
/**
 * PayPal IPN listener and handler.  Also pushes messages into the ActiveMQ queueing system.
 *
 * This is currently designed to act as a mechanism for pushing transactions received from PayPal's
 * IPN system into a 'pending' queue from ActiveMQ.  Once a transaction is verified, it is removed
 * from the pending queue and pushed into a 'verified' queue.  If it is not verified, a copy is left
 * in the pending queue.  This particular logic takes place in execute().
 *
 * Generally speaking, this should likely be abstracted to allow for more flexible use cases, as what
 * is outlined above is pretty specific, but most of the other methods should allow for some flexibility -
 * particularly if you were to subclass this.
 * 
 * Also, this is close to being useable with other queueing systems that can talk Stomp.  However, this 
 * currently employs some things that are likely unique to ActiveMQ, namely setting some custom header
 * information for items going into a pending queue and then using ActiveMQ 'selectors' to pull out
 * a specific message.
 *
 * Does not actually require Mediawiki to run, can be run as stand alone or can be integrated 
 * with a Mediawiki extension.  See StandaloneListener.php.example for a guide on how to do this.
 *
 * Configurable variables:
 *	log_level => $int // 0, 1, or 2 (see constant definitions below for options)
 *  stomp_path => path to Stomp.php
 *  pending_queue => the queue to push pending items to
 *  verified_queue => the queue to push verfiied items to
 *  activemq_stomp_uri => the URI for the activemq broker
 *  contrib_db_host => the hostname where the contribution_tracking table lives
 *  contrib_db_username => the username for the db where contribution_tracking lives
 *  contrib_db_password => the pw for accessing the db where contribution_tracking lives
 *  conrtib_db_name => the db name where contribution_tracking lives
 *
 * Note that the contrib_db* variables are likely of no use to you, unless you're using CiviCRM with Drupal and
 * are using a special contribution tracking module... So if you're not doing that, you can likely 
 * leave those out of your config.
 *
 * PayPal IPN docs: https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_admin_IPNIntro
 *
 * @author Arthur Richards <arichards@wikimedia.org>
 * @TODO: 
 *		add output for DB connection/query
 *		abstract out the contribution_tracking stuff so this is more flexible?
 */

/** Set available log levels **/
DEFINE( 'LOG_LEVEL_QUIET', 0 ); // output nothing
DEFINE( 'LOG_LEVEL_INFO', 1 ); // output minimal info
DEFINE( 'LOG_LEVEL_DEBUG', 2 ); // output lots of info

class PaypalIPNProcessor {

	/**
	 * @var string set the apropriate logging level
	 */
	protected $log_level = LOG_LEVEL_INFO;

	/**
	 * @var string log for messages not intended for us
	 */
	protected $notus_log = NULL;
	
	/**
	 * @var string path to Stomp
	 */
	protected $stomp_path = "../../activemq_stomp/Stomp.php";

	/**
	 * @var string path to pending queue
	 */
	protected $pending_queue = array(
		'default' => '/queue/pending_paypal',
		'recurring' => '/queue/pending_paypal_recurring',
	);

	/**
	 * @var string path to the verified queue
	 */
	protected $verified_queue = array(
		'default' => '/queue/donations',
		'recurring' => '/queue/donations_recurring',
	);

	/**
	 * @var string path to the refund / chargeback queue
	 */
	protected $refund_queue = array(
		'default' => '/queue/refund-notifications',
	);

	/**
	 * @var string  URI to activeMQ
	 */
	protected $activemq_stomp_uri = 'tcp://localhost:61613';

	/** 
	 * @var resource a file system pointer resource (usually made with fopen()) 
	 */
	protected $output_handle = NULL;
	
	/**
	 * @var int Number of times to retry verification with paypal on failure 
	 */
	protected $verification_retry_count = 0;
	
	/**
	 * @var int Number of retries that will trigger an email (fail or eventual pass)
	 */
	protected $verification_email_retry_minimum = 0;

	/**
	 * @var string A unique ID to identify a message
	 */
	public $tx_id = NULL;

	/**
	 * @var bool True if this is a recurring message
	 */
	protected $recurring = FALSE;

	/**
	 * @var bool True is the transaction is a test message from PayPal
	 */
	protected $test = false;

	/**
	 * @var bool True if this is a staging instance.  If True, test transactions
	 * will not be ignored
	 */
	protected $test_instance = false;

	/**
	 * Class constructor, sets configurable parameters
	 *
	 * @param $opts array of key, value pairs where the 'key' is the parameter name and the
	 *	value is the value you wish to set
	 */
	function __construct( $opts = array() ) {
		// set the log level
		if ( array_key_exists( 'log_level', $opts )) {
			$this->log_level = $opts[ 'log_level' ];
			unset( $opts[ 'log_level'] );
		}

		// prepare the output log file if necessary
		if ( array_key_exists( 'output_handle', $opts )) {
			$this->output_handle = $opts[ 'output_handle' ];
			unset( $opts[ 'output_handle' ] );
		}

		// generate a unique id for the message 2 ensure we're manipulating the correct message later on
		$this->tx_id = time() . '_' . mt_rand(); //should be sufficiently unique...
		
		$this->out( "Loading Paypal IPN processor with log level: " . $this->log_level ); 

		// set parameters
		foreach ( $opts as $key => $value ) {
			$this->{$key} = $value;

			// star out passwords in the log!!!!
			if ( $key == 'contrib_db_password' ) $value = '******';
			
			$this->out( "Setting parameter $key as " . print_r( $value, true ), LOG_LEVEL_DEBUG );
		}

		//prepare our stomp connection
		$this->set_stomp_connection();
	}

	/**
	 * Execute IPN procesing.
	 *
	 * Take the data sent from a PayPal IPN request, verify it against the IPN, then push the
	 * transaction to the queue.  Before verifying the transaction against the IPN, this will
	 * place the transaction originally received in the pending queue.  If the transaction is
	 * verified, it will be removed from the pending queue and placed in an accepted queue.  If
	 * it is not verified, it will be left in the pending queue for dealing with in some other
	 * fashion.
	 *
	 * @param $data Array containing the message received from the IPN, likely the contents of 
	 *	$_POST
	 */
	function execute( $data ) {

		//make sure we're actually getting something posted to the page.
		if ( empty( $data )) {
			$this->out( "Received an empty object, nothing to verify." );
			return;
		}

		// connect to stomp
		$this->set_stomp_connection();

		//push message to pending queue
		$contribution = $this->ipn_parse( $data );

		switch($contribution['txn_type']){
			// the following are related to subscriptions
			// I don't think we currently do anything with them, but sending them
			// to the recurring queue
			case 'recurring_payment_profile_created':
			case 'subscr_cancel':
			case 'subscr_eot':
			case 'subscr_failed':
			case 'subscr_modify':
			case 'subscr_signup':
			// the following mean we got money \o/
			case 'recurring_payment':
			case 'subscr_payment':
				$this->pending_queue = $this->pending_queue['recurring'];
				$this->verified_queue = $this->verified_queue['recurring'];
				break;
			// the rest can go to the default queue
			case 'adjustment':
			case 'cart':
			case 'new_case':
			case 'send_money':
			case 'web_accept':
			case 'merch_pmt':
			case 'express_checkout':
			case 'masspay':
			case 'virtual_terminal':
				$this->pending_queue = $this->pending_queue['default'];
				$this->verified_queue = $this->verified_queue['default'];
				break;
			case 'refund':
				$this->pending_queue = $this->pending_queue['default'];
				$this->verified_queue = $this->refund_queue['default'];
				break;
			default:
				$this->out( "Transaction had an unknown txn_type: " . $contribution['txn_type'], LOG_LEVEL_DEBUG );
				$this->pending_queue = $this->pending_queue['default'];
				$this->verified_queue = $this->verified_queue['default'];
		}

		$headers = array( 'persistent' => 'true', 'JMSCorrelationID' => $this->tx_id );
		$this->out( "Setting JMSCorrelationID: $this->tx_id", LOG_LEVEL_DEBUG );

		$this->out("TEST THEM: " . print_r( $this->test, true ), LOG_LEVEL_DEBUG);
		$this->out("TEST US: " . print_r( $this->test_instance, true ), LOG_LEVEL_DEBUG );

		if ( $this->test && !$this->test_instance ){
			$this->out( "This message has been identified as a test message: " . $this->tx_id, LOG_LEVEL_DEBUG );

			if ( $this->msg_sanity_check( $data ) ){
				$this->out( "The message passed a sanity check", LOG_LEVEL_DEBUG );
			} else {
				$this->out( "The message did not pass a sanity check", LOG_LEVEL_DEBUG );
				$this->out( "\$_POST contents: " . print_r( $data, TRUE ), LOG_LEVEL_DEBUG );
			}

			return; // do not execute the rest
		}

		// do the queueing - perhaps move out the tracking checking to its own func?
		if ( !$this->queue_message( $this->pending_queue, json_encode( $contribution ), $headers )) {
			$this->out( "There was a problem queueing the message to the queue: " . $this->pending_queue );
			$this->out( "Message: " . print_r( $contribution, TRUE ), LOG_LEVEL_DEBUG );
		}

		// define a selector property for pulling a particular msg off the queue
		$properties['selector'] = "JMSCorrelationID = '" . $this->tx_id . "'";

		// pull the message object from the pending queue without completely removing it 
		$this->out( "Attempting to pull mssage from pending queue with JMSCorrelationID = " . $this->tx_id, LOG_LEVEL_DEBUG );
		$msg = $this->fetch_message( $this->pending_queue, $properties );
		if ( $msg ) {
			$this->out( "Pulled message from pending queue: " . $msg, LOG_LEVEL_DEBUG);
		} else {
			$this->out( "FAILED retrieving message from pending queue.", LOG_LEVEL_DEBUG );
			return;
		}
		
		// check that the message is legitimate enough to consume
		if ( !$this->msg_sanity_check( $data )) {
			// remove the message from pending queue
			$this->dequeue_message( $msg );
			$this->out( "Message did not pass sanity check." );
			$this->out( "\$_POST contents: " . print_r( $data, TRUE ), LOG_LEVEL_DEBUG );
			return;
		}

		// push to verified queue
		if ( !$this->queue_message( $this->verified_queue, $msg->body )) {
			$this->out( "There was a problem queueing the message to the quque: " . $this->verified_queue );
			$this->out( "Message: " . print_r( $contribution, TRUE ), LOG_LEVEL_DEBUG );
			return;
		}

		// remove from pending
		$this->dequeue_message( $msg );
	}

	/**
	 * Perform message sanity check
	 *  
	 * A warpper for various message verification methods.
	 * At the moment this includes checking for our required fields and
	 * then verifying the message a authentic against PayPal's IPN service
	 * @param array $data
	 */
	public function msg_sanity_check( $data ) {
		if ( !$this->msg_check_reqd_fields( $data )) {
			return false;
		}
		if ( !$this->ipn_verify( $data )) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Make sure that our criteria are met for a consumable message
	 * 
	 * I realize that the criteria this currenctly checks is probably not fully
	 * complete.  Still pulling together a definition for req'd fields.
	 * This was essentially pulled out of old fundcore_paypal_verify()
	 * @param array $data
	 * @return bool
	 */
	public function msg_check_reqd_fields( $data ) {
		$pass = true;

		if ( $this->recurring ){
			// apparently we just pass it along without checking it
			return $pass;
		}

		if ( !in_array( $data[ 'payment_status' ], array( 'Completed', 'Reversed' ) ) ) {
			$this->out( "Message not marked with a known payment_status." );
			$pass = false;
		}

		if ( $data[ 'payment_status'] === "Completed" && $data[ 'mc_gross' ] <= 0 ) {
			$this->out( "Message has 0 or less in the mc_gross field." );
			$pass = false;
		}

		if ( is_null( $data[ 'payer_email' ] )) {
			$this->out( 'Message has no email address.' );
			$pass = false;
		}
		return $pass;
	}
	
	/**
	 * Verify IPN's message validitiy
	 * 
	 * Yoinked from fundcore_paypal_verify() in fundcore/gateways/fundcore_paypal.module Drupal module
	 * @param $post_data array of post data - the message received from PayPal
	 * @return bool
	 */
	public function ipn_verify( $post_data ) {
		// url to respond to paypal with verification response
		$postback_url = 'https://www.paypal.com/cgi-bin/webscr'; // should this be configurable?
		if ( isset( $post_data[ 'test_ipn' ] )) {
			$postback_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}

		if( array_key_exists( "receiver_email", $post_data ) && $post_data["receiver_email"] != $this->receiver_email ){
			// this transaction is not intended for us, execute alternate workflow logging
			// for debugging and error reporting to PayPal

			$this->out( "Message with txn_id [" . $post_data["txn_id"] . "] has INVALID receiver email: " . $post_data["receiver_email"], LOG_LEVEL_DEBUG );
//			$this->out( "Details: " . print_r( $post_data, TRUE ), LOG_LEVEL_DEBUG );
			$this->out( "ABORTING", LOG_LEVEL_DEBUG );

			if( $this->notus_log ){
				$old_log = $this->output_handle;
				// go ahead and change log files to that we can log the strangeness
				$this->output_handle = fopen( $this->notus_log, 'a' );
				$this->out( "Message with txn_id [" . $post_data["txn_id"] . "] has INVALID receiver email: " . $post_data["receiver_email"], LOG_LEVEL_DEBUG );
				$this->out( "Details: " . print_r( $post_data, TRUE ), LOG_LEVEL_DEBUG );
				$this->out( "Not attempting to verify the message with PayPal", LOG_LEVEL_DEBUG );
				fclose( $this->output_handle );
				// go back to the old log file
				$this->output_handle = $old_log;

				// send email to configured recipients notifying them of the PayPal idiocy
				if ( $this->email_recipients && count( $this->email_recipients ) ) {
					if ( $this->customer_service_email && count( $this->customer_service_email ) ) {
						$this->email_recipients = array_merge( $this->email_recipients, $this->customer_service_email );
					}
					$to = implode( ", ", $this->email_recipients );

					// prevent emailing some donor data, including email and name so that
					// the person can be contacted and informed of potential fraud
					$dont_email = array(
						'address_street',
						'address_zip',
					);
					foreach ( $dont_email as $dont ){
						if ( array_key_exists( $dont, $post_data ) ){
							unset($post_data[$dont]);
						}
					}

					$subject = "IPN Listener : Not Our Transaction! " . $this->tx_id;
					$msg = "Greetings!\n\n";
					$msg .= "You are receiving this message because fraudulent transaction was posted to the ";
					$msg .= "PayPal IPN listener! In other words:\n";
					$msg .= "INVALID RECEIVER EMAIL ADDRESS\n\n";

					$msg .= "The contents of the original payload are below, minus some account data:\n\n";
					$msg .= print_r( $post_data, true );
					$msg .= "\n\n";
					$msg .= "The IPN listener-assigned trxn id for this transaction is: " . $this->tx_id . "\n\n";

					$msg .= "Down with Electronic Robin Hood!\n\n";

					$msg .= "Love always,\n";
					$msg .= "Your faithful IPN listener";
					mail( $to, $subject, $msg );
					$this->out( "Invalid Receiver email sent to " . $to );
				}
			}
			return false;
		}

		// respond with exact same data/structure + cmd=_notify-validate
		$attr = $post_data;
		$attr[ 'cmd' ] = '_notify-validate';
		$paypal_txn_id = '[No paypal ID found]';
		if (array_key_exists('txn_id', $post_data)){
			$paypal_txn_id = '[' . $post_data['txn_id'] . ']';
		}
							    
		// send the message back to PayPal for verification
		$status = null;
		$tries = 0;
		$errors_text = '';
		while ( $status != 'VERIFIED' && $tries < $this->verification_retry_count ){
			//we were seeing about a 10% total failure rateon each try, so 
			//7 times *should* be about .00001% failure...
			$status = $this->curl_download( $postback_url, $attr );
			++$tries;
			if ( $status != 'VERIFIED' ){
				$errors_text .= "Attempt $tries came back with a status of $status\n";
				usleep(250000);
			}
		}
		
		if ($status != 'VERIFIED' || $tries > $this->verification_email_retry_minimum ) { //I don't want to hear about most of them.
			//send the email.
			$recovered = false;
			if ($status != 'VERIFIED'){
				$this->out( "The message $paypal_txn_id could not be verified by PayPal (in $tries)." );
				$this->out( "Returned with status: $status", LOG_LEVEL_DEBUG );
			} else {
				$recovered = true;
				$this->out( "The message $paypal_txn_id was eventually verified by PayPal (in $tries)." );
				$this->out( $errors_text, LOG_LEVEL_DEBUG );
			}
			
			//prevent emailing donor data
			$dont_email = array(
				'address_street',
				'address_zip',
				'first_name',
				'address_name',
				'payer_email',
				'last_name',
			);
			foreach ( $dont_email as $dont ){
				if ( array_key_exists( $dont, $post_data ) ){
					unset($post_data[$dont]);
				}
			}
			
			// send email to configured recipients notifying them of the PayPal verification failure
			if ( $this->email_recipients && count( $this->email_recipients )) {
				$to = implode( ", ", $this->email_recipients );
				if ($recovered){
					$subject = "IPN Listener verification failure RECOVERED in $tries for message " . $this->tx_id;
				} else {
				$subject = "IPN Listener verification failure for message " . $this->tx_id;
				}
				$msg = "Greetings!\n\n";
				if ($recovered){
					$msg .= "You are receiving this message because a transaction that was posted to the ";
					$msg .= "PayPal IPN listener failed PayPal verification " . ( $tries - 1 ) . " times, ";
					$msg .= "but then magically healed itself on try $tries.\n";
					$msg .= "...Imagine that.\n";
					$msg .= "$errors_text\n";
				} else {
					$msg .= "You are receiving this message because a transaction that was posted to the ";
					$msg .= "PayPal IPN listener failed PayPal verification with the following status:\n";
					$msg .= "'$status'\n\n";
					$msg .= "Previous Attempts: \n$errors_text\n";
				}

				$msg .= "The contents of the original payload are below, minus some donor data:\n\n";
				$msg .= print_r( $post_data, true );
				$msg .= "\n\n";
				$msg .= "The IPN listener-assigned trxn id for this transaction is: " . $this->tx_id . "\n\n";
				if (!$recovered){
					$msg .= "Good luck figuring out wtf happened!\n\n";
				}
				$msg .= "Love always,\n";
				$msg .= "Your faithful IPN listener";
				mail( $to, $subject, $msg );
				$this->out( "Verification failure email sent to " . $to );
			}
			
			return false;
		} else {
			$this->out( "The message $paypal_txn_id was verified by PayPal (in $tries - no email)." );
		}
		
		return true;
	}

	/**
	 * Parse the PayPal message/post data into the format we need for ActiveMQ
	 *
	 * @param $post_data array containing the $_POST data from PayPal
	 * @return array containing the parsed/formatted message for stuffing into ActiveMQ
	 */
	public function ipn_parse( $post_data ) {
		$this->out( "Attempting to parse: " . print_r( $post_data, TRUE ), LOG_LEVEL_DEBUG );
		$contribution = array();

		$timestamp = strtotime($post_data['payment_date']);

		if ( array_key_exists( 'txn_type', $post_data ) ) {
			$contribution['txn_type'] = $post_data['txn_type'];
		} elseif ( array_key_exists( 'payment_status', $post_data ) && $post_data['payment_status'] === "Reversed" ) {
			// refund, chargeback, or reversal
			$contribution['txn_type'] = "refund";

			$contribution['gateway_parent_id'] = $post_data['parent_txn_id'];
			$contribution['gateway_refund_id'] = $post_data['txn_id'];
			$contribution['gross_currency'] = $post_data['mc_currency'];
			$contribution['type'] = $post_data['reason_code'];
		}

		if ( array_key_exists( 'test_ipn', $post_data ) && (int)$post_data['test_ipn'] === 1 ){
			$this->test = true;
		}

		if ( substr( $contribution[ 'txn_type' ], 0, 7 ) == 'subscr_' ) {
			$this->recurring = TRUE;
			// apparently we don't do anything for recurring stuff ::sigh::
			return $post_data;
		}

		// get the database connection to the tracking table
		$this->contribution_tracking_connection();
		$tracking_data = $this->get_tracking_data( $post_data['custom'] );
		if ( !$tracking_data ) { //we have a problem! The received contribution tracking id does not match anything in the db...
			$this->out( "There is no contribution ID associated with this transaction." );
		}
		$contribution['contribution_tracking_id'] = $post_data['custom'];
		$contribution['optout'] = $tracking_data['optout'];
		$contribution['anonymous'] = $tracking_data['anonymous'];
		$contribution['comment'] = $tracking_data['note'];
		$contribution['email'] = $post_data['payer_email'];
		$contribution['language'] = $tracking_data['language'];
		
		// Premium info
		$contribution['size'] = $post_data['option_selection1'];
		$contribution['premium_language'] = $post_data['option_selection2'];
		
		// Contact info
		$contribution['first_name'] = $post_data['first_name'];
		$contribution['last_name'] = $post_data['last_name'];
		$split = split("\n", str_replace("\r", '', $post_data['address_street']));
		$contribution['street_address'] = $split[0];
		$contribution['supplemental_address_1'] = $split[1];
		$contribution['city'] = $post_data['address_city'];
		$contribution['state_province'] = $post_data['address_state'];
		$contribution['country'] = $post_data['address_country_code'];
		$contribution['postal_code'] = $post_data['address_zip'];
		
		// Shipping info (address same as above since PayPal only passes 1 address)
		$split = split(" ", $post_data['address_name']);
		$contribution['last_name_2'] = array_pop($split);
		$contribution['first_name_2'] = implode(" ", $split);
		$split = split("\n", str_replace("\r", '', $post_data['address_street']));
		$contribution['street_address_2'] = $split[0];
		$contribution['supplemental_address_2'] = $split[1];
		$contribution['city_2'] = $post_data['address_city'];
		$contribution['state_province_2'] = $post_data['address_state'];
		$contribution['country_2'] = $post_data['address_country_code'];
		$contribution['postal_code_2'] = $post_data['address_zip'];
		
		$contribution['gateway'] = ( strlen( $post_data[ 'gateway' ] )) ? $post_data[ 'gateway' ] : 'paypal';
		$contribution['gateway_txn_id'] = $post_data['txn_id'];
		$contribution['original_currency'] = $post_data['mc_currency'];
		$contribution['original_gross'] = $post_data['mc_gross'];
		$contribution['fee'] = $post_data['mc_fee'];  
		$contribution['gross'] = $post_data['mc_gross']; 
		$contribution['net'] = $contribution['gross'] - $contribution['fee'];
		$contribution['date'] = $timestamp;
		
		return $contribution;
	}

	/**
	 * Connect to a URL, send optional post variables, return data
	 *
	 * Yoinked from _fundcore_paypal_download in fundcore/gateways/fundcore_paypal.module Drupal module
	 * @param $url String of the URL to connect to
	 * @param $vars Array of POST variables
	 * @return String containing the output returned from Server
	 */
	public function curl_download( $url, $vars = NULL ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		if ($vars !== NULL) {
			$post_string = '';
			foreach( $vars as $field => $value ){
				if( function_exists( 'get_magic_quotes_gpc' ) == true && get_magic_quotes_gpc() == 1){
					$value = urlencode( stripslashes( $value ) );
				}else{
					$value = urlencode( $value );
				}
				$post_string .= $field . '=' . $value . '&';
			}
			$post_string .= "cmd=_notify-validate";

			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_string );
		}
		
		$i = 0;
		
		while (++$i <= 3){
			$data = curl_exec($ch);
			$header = curl_getinfo($ch);
			
			if ( $header['http_code'] != 200 && $header['http_code'] != 403 ){
				//paypal blow'd up.
				sleep( 1 );
			}
			
			if (!$data) {
				$data = curl_error($ch);
				$this->out( "Curl error: " . $data );
			} else {
				break;
			}
			
		}
		curl_close($ch);
		return $data;
	}

	/** 
	 * Establishes a connection to the stomp listener
	 *
	 * Stomp listner URI set in config options (via command line or localSettings.php).
	 * If a connection cannot be established, will exit with non-0 status.
	 */
	protected function set_stomp_connection() {
		require_once( $this->stomp_path );
		//attempt to connect, otherwise throw exception and exit
		$this->out( "Attempting to connect to Stomp listener: {$this->activemq_stomp_uri}", LOG_LEVEL_DEBUG );
		try {
			//establish stomp connection
			$this->stomp = new Stomp( $this->activemq_stomp_uri );
			$this->stomp->connect();
			$this->out( "Successfully connected to Stomp listener", LOG_LEVEL_DEBUG );
		} catch (Stomp_Exception $e) {
			$this->out( "Stomp connection failed: " . $e->getMessage() );
			exit(1);
		}   
	}

    /** 
     * Send a message to the queue
     *
     * @param $destination string of the destination path for where to send a message
     * @param $message string the (formatted) message to send to the queue
     * @param $options array of additional Stomp options
     * @return bool result from send, FALSE on failure
     */
    public function queue_message( $destination, $message, $options = array( 'persistent' => 'true' )) {
        $this->out( "Attempting to queue message to $destination", LOG_LEVEL_DEBUG );
        $sent = $this->stomp->send( $destination, $message, $options );
        $this->out( "Result of queuing message: $sent", LOG_LEVEL_DEBUG );
        return $sent;
    }   

    /**
     * Remove a message from the queue.
     * @param bool $msg
     */
    public function dequeue_message( $msg ) {
    	$this->out( "Attempting to remove message from pending.", LOG_LEVEL_DEBUG );
		if ( !$this->stomp->ack( $msg )) {
			$this->out( "There was a problem remoivng the verified message from the pending queue: " . print_r( json_decode( $msg, TRUE )));
			return false;
		}
		return true;
    }
    
    /**
     * Fetch latest raw message from a queue
     *
     * @param $destination string of the destination path from where to fetch a message
     * @return mixed raw message (Stomp_Frame object) from Stomp client or False if no msg present
	 */
	public function fetch_message( $destination, $properties = NULL ) {
		$this->out( "Attempting to connect to queue at: $destination", LOG_LEVEL_DEBUG );
		if ( $properties ) $this->out( "With the following properties: " . print_r( $properties, TRUE ));
		$this->stomp->subscribe( $destination, $properties );
		$this->out( "Attempting to pull queued item", LOG_LEVEL_DEBUG );
		$message = $this->stomp->readFrame();
		return $message;
	}

	/**
	 * Establish a connection with the contribution database.
	 *
	 * The properties contrib_db_host, contrib_db_username, contrib_db_password and 
	 * contrib_db_name should be set prior to the execution of this method.
	 */
	protected function contribution_tracking_connection() {
		$this->contrib_db = mysql_connect(
			$this->contrib_db_host,
			$this->contrib_db_username,
			$this->contrib_db_password );
		mysql_select_db( $this->contrib_db_name, $this->contrib_db );
	}

	/**
	 * Fetches tracking data we need to for this transaction from the contribution_tracking table
	 * 
	 * @param int the ID of the transaction we care about
	 * @return array containing the key=>value pairs of data from the contribution_tracking table
	 */
	protected function get_tracking_data( $id ) {
		//sanitize the $id
		$id = mysql_real_escape_string( $id );
		$query = "SELECT * FROM contribution_tracking WHERE id=$id";
		$this->out( "Preparing to run query on contribution_tracking: $query", LOG_LEVEL_DEBUG );
		$result = mysql_query( $query );
		$row = mysql_fetch_assoc( $result );
		$this->out( "Query result: " . print_r( $row, TRUE ), LOG_LEVEL_DEBUG );
		return $row;
	}

	/**
	 * Formats text for output.
	 *
	 * @param $msg String a message to output.
	 * @param $level the Level at which the message should be output.
	 */
	protected function out( $msg, $level=LOG_LEVEL_INFO ) {
		$out = NULL;

		// format the output message if the apropriate log level is set
		if ( $this->log_level >= $level ) $out = date( 'c' ) . "\t" . $this->tx_id . "\t" . $msg . "\n";

		// if we have an output resource handle, write to the resource.  otherwise, echo
		if ( $this->output_handle ) {
			fwrite( $this->output_handle, $out );
		} else {
			echo $out;
		}
	}

	public function __destruct() {
		$this->out( "Exiting gracefully." );
	}
}
