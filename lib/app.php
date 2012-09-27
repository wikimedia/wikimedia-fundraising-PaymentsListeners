<?php

require_once 'stomp.php';
require_once 'tracking.php';
require_once 'failmail.php';
require_once 'logging.php';

abstract class PaymentListener
{
    /**
     * Parse the processor data into the format we need for the message queue
     */
    abstract protected function parse_data($data);

    /**
     * Make sure that our criteria are met for a consumable message.  A failure
     * will condemn the message to the pending queue.  This function can either
     * return FALSE, resulting in a generic error message, or can throw an exception.
     */
    abstract protected function msg_sanity_check($contribution);
}

class BaseListener
{
    protected $pop_msgs = array();

	/**
	 * An array of keys (either original message, or our own) that we should always deliberately remove from emails
	 */
	var $dont_email = array();

	/**
	 * The gateway we are.
	 */
	var $gateway = 'undefined';

	/**
	 * An array of the keys that the gateway could possibly be using for their
	 * own primary keys, to identify either the transaction, or the specific
	 * message they have just sent us.
	 * These could be the keys they send us, or our normalized keys.
	 * These will be listed in order of preference and/or likelihood.
	 */
	var $gateway_pks = array();

    function __construct($opts = array())
    {
        // generate a unique id for this run to ensure we're manipulating the correct message later on
        $this->tx_id = time() . '_' . mt_rand();

        $this->config = $this->load_config($opts);

        Logger::init( get_called_class(), $this->config['log_level'], $this->tx_id );

        foreach ( $this->config as $key => $value )
        {
            if (strstr($key, "password"))
                $value = '******';

            Logger::log( 'debug', "Setting parameter $key as " . print_r( $value, true ) );
        }

        Logger::log( 'info', "Loading ".get_class($this)." processor with log level: " . $this->config['log_level'] ); 

        $this->tracking = new ContributionTracking($this->config);
        $this->queue = new StompQueue($this->config);
    }

    protected function load_config($opts = array())
    {
        $rootdir = dirname(__FILE__).'/..';
        global $config_defaults, $config;
        require_once($rootdir.'/config_defaults.php');
        include_once($rootdir.'/config.php');

        $out = $config_defaults;
        if (!empty($config))
            $out = array_merge($out, $config);
        $out = array_merge($out, $opts);

        // global config can be overridden by gateway name, and then
        // by specific class
        if ( array_key_exists( $this->gateway, $out ) ) {
            $out = array_merge( $out, $out[ $this->gateway ] );
        }

        if ( array_key_exists( get_called_class(), $out ) ) {
            $out = array_merge( $out, $out[ get_called_class() ] );
        }

        return $out;
    }

    /**
     * process incoming data
     *
     * Take the data sent from an incoming asynchronous payment processor
     * request, verify it, then push the transaction to the queue.  The
     * transaction will first be pushed to the pending queue, and iff it
     * is verified, will be removed and pushed into the accepted queue.
     *
     * @param $data Array containing the message received from the processor
     */
    function execute( $data )
    {
        try {
            $this->validate_remote_ip();

            //make sure we're actually getting something posted to the page.
            if ( empty( $data )) {
                throw new Exception("Received an empty object, nothing to verify.");
            }

            $contribution = $this->parse_data( $data );

            $this->queue_pending( $contribution );

            // check that the message is legitimate enough to consume
            if ( !$this->msg_sanity_check( $contribution )) {
                throw new Exception("Message did not pass sanity check.");
            }

            $this->queue->queue_message( $this->config['verified_queue'], $contribution );
        } catch (Exception $ex)
        {
            Logger::log( 'error', $ex->getMessage() );
            if ( !empty( $contribution ) )
            {
                $contribution['listener_error'] = $ex->getMessage();
                $this->fail( $contribution );
            }
            elseif (!empty($data))
            {
                $data['listener_error'] = $ex->getMessage();
                $this->fail( $data );
            }
        }
    }

    protected function validate_remote_ip() {
        if ( empty( $this->config['ip_whitelist'] ) ) {
            Logger::log( 'info', "No IP whitelist specified." );
            return;
        }
        $headers = getallheaders();
        if ( !$headers || !array_key_exists( 'X-Real-IP', $headers ) ) {
            throw new Exception( "Unexpected platform issue when trying to get remote box's IP" );
        }
        $remote_ip = $headers['X-Real-IP'];

        if( !filter_var( $remote_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ){
            throw new Exception( "Bizarre remote IP address: {$remote_ip}" );
        }

        foreach( (array)$this->config['ip_whitelist'] as $ip ){
            if( $remote_ip === $ip ){
                return;
            }
            if( count( explode( '/', $ip ) ) === 2 ){
                list( $network_ip, $block ) = explode( '/', $ip );
                if( !filter_var( $network_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) || !filter_var( $block, FILTER_VALIDATE_INT, array( 'min_range' => 0, 'max_range' => 32 ) ) ){
                    throw new Exception( "IP whitelist contains garbage: {$ip}" );
                }
                // check the address to make sure it is a proper network address
                $network_long = ip2long( $network_ip );
                $mask_long = ~ ( pow( 2, ( 32 - $block ) ) - 1 );

                $remote_long = ip2long( $remote_ip );

                if( ( $remote_long & $mask_long ) === ( $network_long & $mask_long ) ){
                    return; // the remote IP address is in this range
                }
            }
        }

        // we have fallen through everything in the whitelist, throw
        throw new Exception( "Received a connection from a bogus IP: {$remote_ip}, agent: {$_SERVER['HTTP_USER_AGENT']}" );
    }

    protected function queue_pending($contribution)
    {
        //push message to pending queue
        $headers = array( 'persistent' => 'true', 'JMSCorrelationID' => $this->tx_id );
        Logger::log( 'debug', "Setting JMSCorrelationID: $this->tx_id" );

        $this->queue->queue_message( $this->config['pending_queue'], json_encode( $contribution ), $headers );

        $this->pop_msgs[] = array(
            'queue' => $this->config['pending_queue'],
            'JMSCorrelationID' => $this->tx_id,
        );
    }

    function fail($data)
    {
        $this->queue->queue_message($this->config['failed_queue'], json_encode($data));

        failmail( 
			$this->clean_data_for_email( $data ),
            $this->config['failed_queue'],
            $this->config['email_recipients'],
            get_class($this),
            $this->tx_id,
			$this->get_gateway_pks( $data ),
			$this->gateway
        );
    }

	/**
	 * Removes all keys in $this->dont_email from $data. To be used just prior
	 * to sending a failmail.
	 * @param array $data
	 * @return array
	 */
	function clean_data_for_email( $data ){
		if ( !empty( $this->dont_email ) && is_array( $this->dont_email ) ){
			foreach ( $this->dont_email as $dont ){
				unset( $data[$dont] );
			}
		}
		return $data;
	}

	/**
	 * Get the primary keys by which the gateway will be able to uniquely 
	 * identify a transaction or message. Very useful for failmail to know this 
	 * and present it prominently. 
	 * @param array $data A message we are trying to process. Could be anywhere 
	 * from the original message, to a normalized thing we're trying to tell 
	 * ourselves.  
	 * @return array The primary key(s) that can be used to identify the message
	 * or transaction we're working on.
	 */
	function get_gateway_pks( $data ){
		$pks = array();
		if ( !empty( $this->gateway_pks ) && is_array( $this->gateway_pks ) ){
			foreach ( $data as $key => $val ){
				if ( in_array( $key, $this->gateway_pks ) ){
					$pks[$key] = $val;
				}
			}
		}
		return $pks;
	}

    protected function copy_tracking_data(&$contribution)
    {
        # n.b. this function is probably incomplete, and hasn't been used yet
        $tracking_data = $this->tracking->get_tracking_data($contribution['gateway_txn_id']);
        if ($tracking_data)
        {
            //$contribution['contribution_tracking_id'] =
            $contribution['optout'] = $tracking_data['optout'];
            $contribution['anonymous'] = $tracking_data['anonymous'];
            $contribution['comment'] = $tracking_data['note'];
            $contribution['language'] = $tracking_data['language'];
        }
        else
        {
            //we have a problem! The received contribution tracking id does not match anything in the db...
            Logger::log( 'error', "There is no contribution ID associated with this transaction." );
        }
    }

    protected function merge_limbo_data(&$contribution)
    {
        $properties = array(
            'selector' => "JMSCorrelationID = '{$contribution['gateway']}-{$contribution['gateway_txn_id']}'",
        );
        $msg = $this->queue->fetch_message($this->config['limbo_queue'], $properties);
        if ($msg)
        {
            foreach (json_decode($msg->body) as $key => $value)
            {
                if (!array_key_exists($key, $contribution))
                    $contribution[$key] = $value;
            }
            $this->pop_msgs[] = $msg;
            return true;
        }
    }

    function __destruct() {
        foreach ($this->pop_msgs as $msg ) {
            $this->queue->dequeue_message( $msg );
        }

        Logger::log( 'info', "Exiting gracefully." );
    }
}
