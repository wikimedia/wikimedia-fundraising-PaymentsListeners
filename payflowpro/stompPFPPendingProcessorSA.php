<?php

/**
 * Processes pending PayflowPro transactions in a queueing service using Stomp
 *
 * This is the stand alone version
 *
 * This was built to verify pending PayflowPro transactions in an ActiveMQ queue system.  
 * It pulls a transaction out of a 'pending' queue and submits the message to PayflowPro
 * for verification.  If PayflowPro verifies the transaction, it is then passed off to a
 * 'confirmed' queue for processing elsewhere.  If PayflowPro rejects for a small variety 
 * of reasons (ones that require some user intervention), the message is reloaded to the 
 * queue.  If PayflowPro completely rejects the message, it is pruned from the queue 
 * altogether.
 *
 * This performs some logging (depending on the log_level setting), which if not set to 0 
 * just gets output to the screen.
 *
 * Config options:
 * 	$valid_options = array (
 * 					'pfp_url', //payflow pro url for validation
 * 					'pfp_partner_id', // payflow pro partner id
 * 					'pfp_vendor_id', // payflow pro vendor id
 * 					'pfp_user_id', //payflow pro user id
 * 					'pfp_password', // payflow pro password
 * 					'activemq_stomp_uri', // stomp URI
 * 					'activemq_pending_queue', // path to pending queue
 * 					'activemq_confirmed_queue', // path to confirmed queue
 * 					'batch_size', // batch size
 * 					'log_level', // log level
 * 					'stomp_path', // path to Stomp.php
 * 					'output_handle' ); // file system pointer resource for writing output to a log file
 *
 * @author: Arthur Richards <arichards@wikimedia.org>
 * @author: Katie Horn <khorn@wikimedia.org>
 */
define('LOG_LEVEL_QUIET', 0); // disables all logging
define('LOG_LEVEL_INFO', 1); // some useful logging information
define('LOG_LEVEL_DEBUG', 2);

 // verbose logging for debug

class StompPFPPendingProcessorSA {

	/** If TRUE, output extra information for debug purposes * */
	protected $log_level = LOG_LEVEL_INFO;
	/** Holds our Stomp connection instance * */
	protected $stomp;
	/** The number of items to process * */
	protected $batch_size = 50;
	/** Define the path to Stomp.php * */
	protected $stomp_path;
	protected $message_cache = array();

	public function __construct($config) {
		// load the configuration options
		$this->load_config_options($config);

		// require the Stomp file
		require_once( $this->stomp_path );
	}

	public function execute() {
		$this->log("Pending queue processor bootstrapped and ready to go!");

		// estamplish a connection to the stomp listener
		if (!$this->set_stomp_connection()) {
			return false;
		}

		$this->log("Preparing to process up to {$this->batch_size} pending transactions.", LOG_LEVEL_DEBUG);

		// batch process pending transactions
		for ($i = 0; $i < $this->batch_size; $i++) {
			// empty pending_transaction
			if (isset($message))
				unset($message);

			// fetch the latest pending transaction from the queue (Stomp_Frame object)
			$message = $this->fetch_message($this->activemq_pending_queue);
			// if we do not get a pending transaction back...
			if (!$message) {
				$this->log("There are no more pending transactions to process.", LOG_LEVEL_DEBUG);
				break;
			}

			// the message is in it's raw format, we need to decode just it's body
			$pending_transaction = json_decode($message->body, TRUE);

			if (!array_key_exists($pending_transaction['gateway_txn_id'], $this->message_cache)) {
				$this->message_cache[$pending_transaction['gateway_txn_id']] = array(
					'raw' => $message,
					'body' => $pending_transaction,
				);
			} else {
				//it's a duplicate. Ack the thing and move on.
				$this->log('Duplicate Transaction Found: ' . $pending_transaction['gateway_txn_id'], LOG_LEVEL_DEBUG);
				$ack_response = $this->ack_message($message);
			}
		}

		foreach ($this->message_cache as $transaction => $data) {
			$this->log("Pending transaction: " . print_r($data['body'], TRUE), LOG_LEVEL_DEBUG);

			$result_code = false;
			if (isset($this->pfp_user_id) && $this->pfp_user_id != '') {
				$this->log("PFP USER ID!: $this->pfp_user_id");
				// fetch the payflow pro status of this transaction
				$status = $this->fetch_payflow_transaction_status($data['body']['gateway_txn_id']);

				// determine the result code from the payflow pro status message
				$result_code = $this->parse_payflow_transaction_status($status);
			} else {
				$this->log("No PFP Credentials!");
				if (isset($this->test_mode)) {
					if(isset($this->test_code)){
						$result_code = $this->test_code;
					} else {
						$result_code = 126;
					}
					$this->log("Test mode enabled. Returning PFP code $result_code.");
				}
			}

			// handle the pending transaction based on the payflow pro result code
			if ($result_code !== false) {
				$this->handle_pending_transaction($result_code, json_encode($data['body']));
				sleep(1);  //OMG^2. Yes, even in this position, this is necessary.
				//TODO: better slight pause. I don't want to sleep for a whole second.
				//Certainly not if Paypal already ate that second. Dumb.
				$this->ack_message($data['raw']);
			} else {
				$this->log("No result from Payflow Pro (Transaction " . $data['body']['gateway_txn_id'] . " ). Skipping.");
			}
		}

		$this->log("Processed $i messages. (" . sizeof($this->message_cache) . " unique messages)");
	}

	/**
	 * Fetch latest raw message from a queue
	 *
	 * @param $destination string of the destination path from where to fetch a message
	 * @return mixed raw message (Stomp_Frame object) from Stomp client or False if no msg present
	 */
	protected function fetch_message($destination) {
		$this->log("Attempting to connect to queue at: $destination", LOG_LEVEL_DEBUG);

		$returned = $this->stomp->subscribe($destination, array('ack' => 'client'));
		$this->log(print_r($returned, true) . ": Returned by subscribe");

		$this->log("Attempting to pull queued item", LOG_LEVEL_DEBUG);
		$message = $this->stomp->readFrame();

		if (!empty($message)) {
			$this->log(print_r($message, true), LOG_LEVEL_DEBUG);
		} else {
			$this->log("No message found.", LOG_LEVEL_DEBUG);
		}

		return $message;
	}

	/**
	 * Send a message to the queue
	 *
	 * @param $destination string of the destination path for where to send a message
	 * @param $message string the (formatted) message to send to the queue
	 * @param $options array of additional Stomp options
	 * @return bool result from send, FALSE on failure
	 */
	protected function queue_message($destination, $message, $options = array('persistent' => TRUE)) {
		$this->log("Attempting to queue message...", LOG_LEVEL_DEBUG);
		$sent = $this->stomp->send($destination, $message, $options);
		$this->log("Result of queuing message: $sent", LOG_LEVEL_DEBUG);
		return $sent;
	}

	/**
	 * Send a message to the queue
	 *
	 * @param $message The entire original message to ack
	 * @return bool result from send, FALSE on failure
	 */
	protected function ack_message($message) {
		$this->log("Attempting to ack message...", LOG_LEVEL_DEBUG);
		if (!array_key_exists('redelivered', $message->headers)) {
			$message->headers['redelivered'] = 'true';  //OMG. Srsly.
		}
		$sent = $this->stomp->ack($message);
		$this->log("Result of message ack: $sent", LOG_LEVEL_DEBUG);
		return $sent;
	}

	/**
	 * Fetch the PayflowPro status of a transaction.
	 *
	 * @param $transaction_id string of the original ID of the transaction to status check
	 * @return string containing the raw status message returned by PayflowPro
	 */
	protected function fetch_payflow_transaction_status($transaction_id) {
		$this->log("Transaction ID: $transaction_id", LOG_LEVEL_DEBUG);
		// create payflow query string, include string lengths
		$queryArray = array(
			'TRXTYPE' => 'I',
			'TENDER' => 'C',
			'USER' => $this->pfp_user_id, //$payflow_data['user'],
			'VENDOR' => $this->pfp_vendor_id, //$payflow_data['vendor'],
			'PARTNER' => $this->pfp_partner_id, //$payflow_data['partner'],
			'PWD' => $this->pfp_password, //$payflow_data['password'],
			'ORIGID' => $transaction_id,
			'VERBOSITY' => 'MEDIUM',
		);
		$this->log("PayflowPro query array: " . print_r($queryArray, TRUE), LOG_LEVEL_DEBUG);

		// format the query string for PayflowPro		
		foreach ($queryArray as $name => $value) {
			$query[] = $name . '[' . strlen($value) . ']=' . $value;
		}
		$payflow_query = implode('&', $query);
		$this->log("PayflowPro query array (formatted): " . print_r($payflow_query, TRUE), LOG_LEVEL_DEBUG);

		// assign header data necessary for the curl_setopt() function
 		$order_id = date('ymdH') . rand(1000, 9999); //why?
		if(array_key_exists('HTTP_USER_AGENT', $_SERVER)){
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$user_agent = "blargh?";
		}
		$headers[] = 'Content-Type: text/namevalue';
		$headers[] = 'Content-Length : ' . strlen($payflow_query);
		$headers[] = 'X-VPS-Client-Timeout: 45';
		$headers[] = 'X-VPS-Request-ID:' . $order_id;
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->pfp_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payflow_query);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_POST, 1);

		// As suggested in the PayPal developer forum sample code, try more than once to get a response
		// in case there is a general network issue 
		for ($i = 1; $i <= 3; $i++) {
			$this->log("Attempt #$i to connect to PayflowPro...", LOG_LEVEL_DEBUG);
			$status = curl_exec($ch);
			$headers = curl_getinfo($ch);

			if ($headers['http_code'] != 200 && $headers['http_code'] != 403) {
				sleep(5);
			} elseif ($headers['http_code'] == 200 || $headers['http_code'] == 403) {
				$this->log("Succesfully connected to PayflowPro", LOG_LEVEL_DEBUG);
				break;
			}
		}

		if ($headers['http_code'] != 200) {
			$this->log("No response from PayflowPro after $i attempts.");
			curl_close($ch);
			exit(1);
		}

		curl_close($ch);

		$this->log("PayflowPro reported status: $status", LOG_LEVEL_DEBUG);
		return $status;
	}

	/**
	 * Parse the result code out of PayflowPro's status message.
	 *
	 * This is modified from queue2civicrm_payflow_result() in the Drupal queue2civicrm module.
	 * That code, however, seemed to be cataloging all of the key/value pairs in the status
	 * message.  Since we only care about the result code, that's all I'm looking for.  
	 * Perhaps it is more favorable to return an aray of the key/value pairs in the status
	 * message...
	 *
	 * Note that when doing an 'inquiry' transaction with PayflowPro, the 'RESULT' code is for
	 * inquiry transaction itself.  The result code for the transaction you are inquiring 
	 * about is actually the 'ORIGRESULT' code.
	 *
	 * @status string The full status message returned by a PayflowPro queyry
	 * @return int PayflowPro result code, FALSE on failure
	 */
	protected function parse_payflow_transaction_status($status) {
		// we only really care about the 'ORIGRESULT' portion of the status message
		$result = strstr($status, 'ORIGRESULT');

		// log the result string?
		$this->log("PayflowPro RESULT string: $result", LOG_LEVEL_DEBUG);

		// establish our key/value positions in the string to facilitate extracting the value
		$key_position = strpos($result, '=');
		$value_position = strpos($result, '&') ? strpos($result, '&') : strlen($result);

		$result_code = substr($result, $key_position + 1, $value_position - $key_position - 1);
		if (trim($result_code) != '') {
			$this->log("PayflowPro result code: $result_code", LOG_LEVEL_DEBUG);
		} else {
			$this->log("PayflowPro returned NO result code!");
			return false;
		}
		return $result_code;
	}

	/**
	 * Apropriately handles pending transactions based on the PayflowPro result code
	 *
	 * @param int PayflowPro result code
	 * @param string Formatted message to send to a queue
	 */
	protected function handle_pending_transaction($result_code, $message) {
		$msgarray = json_decode($message, true);
		$this->log("Handling transaction " . $msgarray['gateway_txn_id'] . " with code $result_code");
		switch ($result_code) {
			case "0": // push to confirmed queue
				$this->log("Attempting to push message to confirmed queue: " . print_r($message, TRUE), LOG_LEVEL_DEBUG);
				if ($this->queue_message($this->activemq_confirmed_queue, $message)) {
					$this->log("Succesfully pushed message to confirmed queue.", LOG_LEVEL_DEBUG);
				}
				break;
			case "126": // push back to pending queue - marked as potential fraud
			case "26": //push back to pending queue - log-in information is incorrect
				$this->log("Attempting to push message back to pending queue: " . print_r($message, TRUE), LOG_LEVEL_DEBUG);
				if ($this->queue_message($this->activemq_pending_queue, $message)) {
					$this->log("Succesfully pushed message back to pending queue", LOG_LEVEL_DEBUG);
				}
				break;
			default:
				$this->log("Message ignored: " . print_r($message, TRUE), LOG_LEVEL_DEBUG);
				break;
		}
	}

	/**
	 * Loads configuration options
	 *
	 * @param array An associative array containing configuration otpions
	 */
	protected function load_config_options($config) {
		// Array of available configuration options
		$valid_options = array(
			'output_handle',
			'log_level',
			'pfp_url',
			'pfp_partner_id',
			'pfp_vendor_id',
			'pfp_user_id',
			'pfp_password',
			'activemq_stomp_uri',
			'activemq_pending_queue',
			'activemq_confirmed_queue',
			'batch_size',
			'stomp_path',);

		// loop through our options and set their values, 
		// be sure to only take options that are valid
		foreach ($valid_options as $option) {
			// only process if this option was passed in by the user
			if (!$config[$option])
				continue;

			// set class property with the config option
			$this->$option = $config[$option];
			if ($option == 'pfp_user_id' || $option == 'pfp_password'){
				$this->log($option . " =  [set, but not logged]", LOG_LEVEL_DEBUG);
			} else {
				$this->log($option . " =  " . $this->$option, LOG_LEVEL_DEBUG);
			}
		}
	}

	/**
	 * Establishes a connection to the stomp listener
	 *
	 * Stomp listner URI set in config options (via command line or localSettings.php).
	 * If a connection cannot be established, will exit with non-0 status.
	 */
	protected function set_stomp_connection() {
		//attempt to connect, otherwise throw exception and exit
		$this->log("Attempting to connect to Stomp listener: {$this->activemq_stomp_uri}", LOG_LEVEL_DEBUG);
		try {
			//establish stomp connection
			$this->stomp = new Stomp($this->activemq_stomp_uri);
			$this->stomp->connect();
			$this->log("Successfully connected to Stomp listener", LOG_LEVEL_DEBUG);
			return true;
		} catch (Stomp_Exception $e) {
			$this->log("Stomp connection failed: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Logs messages of less than or equal value to the defined log level.
	 *
	 * Log levels available are defined by the constants LOG_LEVEL_*.  The log level for the script
	 * defaults to LOG_LEVEL_INFO but can be overridden in LocalSettings.php or via a command line
	 * argument passed in at run time.
	 * 
	 * @param $message string containing the message you wish to log
	 * @param $level int of the highest log level you wish to output messages for
	 */
	protected function log($message, $level=LOG_LEVEL_INFO) {
		if ($this->log_level >= $level) {
			$out = date('c') . ": " . $level . " : " . $message . "\n";

			// if we have an output resource handle, write to the resource.  otherwise, echo
			if ($this->output_handle) {
				fwrite($this->output_handle, $out);
			} else {
				echo $out;
			}
		}
	}

	public function __destruct() {
		// clean up our stomp connection
		$this->log("Cleaning up stomp connection...", LOG_LEVEL_DEBUG);
		if (isset($this->stomp))
			$this->stomp->disconnect();
		$this->log("Stomp connection cleaned up", LOG_LEVEL_DEBUG);
		$this->log("Exiting gracefully");
	}

}
