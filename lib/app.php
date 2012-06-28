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
     * will condemn the message to the pending queue.
     */
    abstract protected function msg_sanity_check($msg);
}

class BaseListener
{
    function __construct($opts = array())
    {
        // generate a unique id for this run to ensure we're manipulating the correct message later on
        $this->tx_id = time() . '_' . mt_rand();

        $this->config = $this->load_config($opts);
        Logger::init($this->config['log_level'], $this->config['log_file'], $this->tx_id);

        foreach ( $this->config as $key => $value )
        {
            if (strstr($key, "password"))
                $value = '******';

            Logger::log( "Setting parameter $key as $value.", 'debug' );
        }

        Logger::log( "Loading ".get_class($this)." processor with log level: " . $this->config['log_level'] ); 

        $this->tracking = new ContributionTracking($this->config);
        $this->queue = new StompQueue($this->config);

        $this->pop_limbo_msg = FALSE;
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
            //make sure we're actually getting something posted to the page.
            if ( empty( $data )) {
                throw new Exception("Received an empty object, nothing to verify.");
            }

            $contribution = $this->parse_data( $data );

            //push message to pending queue
            $headers = array( 'persistent' => 'true', 'JMSCorrelationID' => $this->tx_id );
            Logger::log( "Setting JMSCorrelationID: $this->tx_id", 'debug' );

            // do the queueing - perhaps move out the tracking checking to its own func?
            $this->queue->queue_message( $this->config['pending_queue'], json_encode( $contribution ), $headers );

            // define a selector property for pulling a particular msg off the queue
            $properties = array('selector' => "JMSCorrelationID = '{$this->tx_id}'");

            // pull the message object from the pending queue without completely removing it 
            Logger::log( "Attempting to pull message from pending queue with JMSCorrelationID = {$this->tx_id}", 'debug' );
            $msg = $this->queue->fetch_message( $this->config['pending_queue'], $properties );
            if ( $msg ) {
                Logger::log( "Pulled message from pending queue: {$msg->body}", 'debug');
            } else {
                throw new Exception("FAILED retrieving message from pending queue.");
            }
            
            // check that the message is legitimate enough to consume
            if ( !$this->msg_sanity_check( $msg ))
            {
                // add to a failed queue
                $error_message = "Message did not pass sanity check.";
                $body = json_decode($msg->body);
                $body->error = $error_message;
                $this->queue->queue_message($this->config['failed_queue'], json_encode($body));

                // remove the message from pending queue
                $this->queue->dequeue_message( $msg );

                failmail(array(
                    'data' => $data,
                    'failed_queue' => $this->config['failed_queue'],
                    'email_recipients' => $this->config['email_recipients'],
                    'listener_class' => get_class($this),
                    'tx_id' => $this->tx_id
                ));
                throw new Exception($error_message);
            }

            $this->queue->queue_message( $this->config['verified_queue'], $msg->body );

            // remove from pending
            $this->queue->dequeue_message( $msg );
        } catch (Exception $ex)
        {
            Logger::log($ex->getMessage(), 'err');
        }
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
            Logger::log( "There is no contribution ID associated with this transaction." );
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
            $this->pop_limbo_msg = $msg;
            return true;
        }
    }

    function __destruct() {
        if ($this->pop_limbo_msg)
            $this->queue->dequeue_message( $this->pop_limbo_msg );

        Logger::log( "Exiting gracefully." );
    }
}
