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
        $this->config = $this->load_config($opts);
        Logger::init($this->config['log_level'], $this->config['log_file']);

        foreach ( $this->config as $key => $value ) {
            // star out passwords in the log!!!!
            if ( $key == 'contrib_db_password' ) $value = '******';
            
            Logger::log( "Setting parameter $key as $value.", LOG_LEVEL_DEBUG );
        }

        Logger::log( "Loading ".get_class($this)." processor with log level: " . $this->config['log_level'] ); 

        $this->tracking = new ContributionTracking($this->config);
        $this->queue = new StompQueue($this->config);
    }

    function load_config($opts = array())
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
        //make sure we're actually getting something posted to the page.
        if ( empty( $data )) {
            Logger::log( "Received an empty object, nothing to verify." );
            return;
        }

        // generate a unique id for the message to ensure we're manipulating the correct message later on
        $this->tx_id = time() . '_' . mt_rand(); //should be sufficiently unique...

        $contribution = $this->parse_data( $data );

        //push message to pending queue
        $headers = array( 'persistent' => 'true', 'JMSCorrelationID' => $this->tx_id );
        Logger::log( "Setting JMSCorrelationID: $this->tx_id", LOG_LEVEL_DEBUG );

        // do the queueing - perhaps move out the tracking checking to its own func?
        if ( !$this->queue->queue_message( $this->config['pending_queue'], json_encode( $contribution ), $headers )) {
            Logger::log( "There was a problem queueing the message to the queue: " . $this->config['pending_queue'] );
            Logger::log( "Message: " . print_r( $contribution, TRUE ), LOG_LEVEL_DEBUG );
        }

        // define a selector property for pulling a particular msg off the queue
        $properties['selector'] = "JMSCorrelationID = '" . $this->tx_id . "'";

        // pull the message object from the pending queue without completely removing it 
        Logger::log( "Attempting to pull message from pending queue with JMSCorrelationID = " . $this->tx_id, LOG_LEVEL_DEBUG );
        $msg = $this->queue->fetch_message( $this->config['pending_queue'], $properties );
        if ( $msg ) {
            Logger::log( "Pulled message from pending queue: " . $msg, LOG_LEVEL_DEBUG);
        } else {
            Logger::log( "FAILED retrieving message from pending queue.", LOG_LEVEL_DEBUG );
            return;
        }
        
        // check that the message is legitimate enough to consume
        if ( !$this->msg_sanity_check( $data )) {
            // remove the message from pending queue
            $this->queue->dequeue_message( $msg );
            Logger::log( "Message did not pass sanity check." );
            Logger::log( "\$_POST contents: " . print_r( $data, TRUE ), LOG_LEVEL_DEBUG );
            failmail($data);
            return;
        }

        // push to verified queue
        if ( !$this->queue->queue_message( $this->config['verified_queue'], $msg->body )) {
            Logger::log( "There was a problem queueing the message to the quque: " . $this->config['verified_queue'] );
            Logger::log( "Message: " . print_r( $contribution, TRUE ), LOG_LEVEL_DEBUG );
            return;
        }

        // remove from pending
        $this->queue->dequeue_message( $msg );
    }

    function copy_tracking_data($id, &$contribution)
    {
        $tracking_data = $this->tracking->get_tracking_data($id);
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

    public function __destruct() {
        Logger::log( "Exiting gracefully." );
    }
}
