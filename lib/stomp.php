<?php

/**
 * should be compatible with pecl Stomp and our vendors/stomp_php library
 */
class StompQueue
{
    function __construct($config)
    {
        if (!empty($config['stomp_path']))
            require_once( $config['stomp_path'] );

        Logger::log( 'debug', "Attempting to connect to Stomp listener: {$config['activemq_stomp_uri']}" );

        $this->stomp = new Stomp( $config['activemq_stomp_uri'] );
        if (method_exists($this->stomp, 'connect'))
            $this->stomp->connect();

        Logger::log( 'debug', "Successfully connected to Stomp listener" );

        if (!empty($config['stomp_timeout']))
        {
            $this->stomp->setReadTimeout($config['stomp_timeout']);
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
    public function queue_message( $destination, $message, $headers = array( 'persistent' => 'true' ) )
    {
        Logger::log( 'debug', "Attempting to queue message to $destination" );
        $sent = $this->stomp->send( $destination, $message, $headers );
        if (!$sent) {
            throw new Exception( "There was a problem queueing a message: {$destination} -- " . print_r( $message, true ) );
        }
    }   

    /**
     * Remove a message from the queue.
     * @param bool $msg
     */
    public function dequeue_message( $msg ) {
        Logger::log( 'debug', "Attempting to remove message." );

        if ( is_array( $msg ) ) {
            // we must search for the message by headers
            $queue = $msg['queue'];
            unset( $msg['queue'] );
            $msg = $this->fetch_message( $queue, $msg );
        }

        if ( !$msg || !$this->stomp->ack( $msg )) {
            throw new Exception( "There was a problem removing a message from the queue: " . print_r( $msg, TRUE ) );
        }
    }

    /**
     * Fetch latest raw message from a queue
     *
     * @param $destination string of the destination path from where to fetch a message
     * @return mixed raw message (Stomp_Frame object) from Stomp client or False if no msg present
     */
    public function fetch_message( $destination, $properties = NULL ) {
        Logger::log( 'debug', "Attempting to connect to queue at: $destination" );
        if ( $properties )
            Logger::log( 'debug', "With the following properties: " . print_r( $properties, TRUE ) );
        $this->stomp->subscribe( $destination, $properties );
        Logger::log( 'debug', "Attempting to pull queued item" );
        $message = $this->stomp->readFrame();
        return $message;
    }
}
