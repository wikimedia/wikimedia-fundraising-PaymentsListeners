<?php
/**
 * Wikimedia Foundation
 *
 * LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 *
 * @category	Fundraising
 * @package		Fundraising_QueueHandling
 * @subpackage	Fundraising_QueueHandling_Listener
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 * @since		r462
 * @author		Jeremy Postlethwaite <jpostlethwaite@wikimedia.org>
 */

/**
 *
 * @todo
 * - Implement Abstract
 *
 * @category	Fundraising
 * @package		Fundraising_QueueHandling
 * @subpackage	Fundraising_QueueHandling_Listener
 */
abstract class Listener_Adapter_Abstract
{

	/**
	 * activeMqStompUri
	 *
	 * This is tunneled instance of activeMQ
	 *
	 * Port 61613 is the default install for ActiveMQ 5.4.2
	 *
	 * @var string activeMqStompUri
	 */
	protected $activeMqStompUri = 'tcp://localhost:61613';

	/**
	 * The contribution
	 *
	 * @var array contribution
	 */
	protected $contribution = array();

	/**
	 * The contribution
	 *
	 * @var array contribution
	 */
	protected $data = array();

	/**
	 * The log file
	 *
	 * @var string logFile
	 */
	protected $logFile = '';

	/**
	 * The log level
	 *
	 * @see Listener::LOG_LEVEL_EMERG
	 * @see Listener::LOG_LEVEL_ALERT
	 * @see Listener::LOG_LEVEL_CRIT
	 * @see Listener::LOG_LEVEL_ERR
	 * @see Listener::LOG_LEVEL_WARN
	 * @see Listener::LOG_LEVEL_NOTICE
	 * @see Listener::LOG_LEVEL_INFO
	 * @see Listener::LOG_LEVEL_DEBUG
	 *
	 * @var integer logLevel
	 */
	protected $logLevel = Listener::LOG_LEVEL_ERR;

	/**
	 * messageFromPendingQueue
	 *
	 * This is message fetched from the pending queue
	 *
	 * @var StompFrame $messageFromPendingQueue
	 */
	protected $messageFromPendingQueue;

	/**
	 * outputHandle
	 *
	 * This is a resource created by fopen.
	 *
	 * @var resource outputHandle
	 */
	protected $outputHandle;

	/**
	 * queuePending
	 *
	 * This is path to pending queue
	 *
	 * @var string queuePending
	 */
	protected $queuePending = '';

	/**
	 * queueVerified
	 *
	 * This is path to verified queue
	 *
	 * @var string queueVerified
	 */
	protected $queueVerified = '';

	/**
	 * stomp
	 *
	 * The instance of Stomp
	 *
	 * @var Stomp $stomp
	 */
	protected $stomp;

	/**
	 * stompPath
	 *
	 * This is path to Stomp
	 *
	 * Stomp can be placed in the library folder.
	 *
	 * @var string stompPath
	 */
	protected $stompPath = 'Stomp.php';

	/**
	 * txId
	 *
	 * This is the transaction id
	 *
	 * @var string txId
	 */
	protected $txId = '';

	/**
	 * Constructor
	 *
	 * @param array $parameters The adapter parameters
	 */
	public function __construct( $parameters )
	{
		// Extract parameters.
		extract( $parameters );

		// Set the stomp path if passed from parameters.
		if ( isset( $activeMqStompUri ) ) {
			$this->setActiveMqStompUri( $activeMqStompUri );
		}

		// Set log level if passed from parameters.
		if ( isset( $logLevel ) ) {
			$this->setLogLevel( $logLevel );
		}

		// Set log file if passed from parameters.
		if ( isset( $logFile ) ) {
			$this->setLogFile( $logFile );
		}

		$message = 'Loading ' . $this->getAdapterType() . ' processor with log level: ' . $this->getLogLevel();
		$this->log( $message );

		// Set the stomp path if passed from parameters.
		if ( isset( $stompPath ) ) {
			$this->setStompPath( $stompPath );
		}

		// Create transaction id
		$this->setTxId();
	}

	/**
	 * Destructor
	 *
	 * Performs
	 * - closes logs
	 */
	public function __destruct()
	{
		// Close log if it was opened.
		$this->closeOutputHandle();
	}

	/**
	 * Parse the data and format for Contribution Tracking
	 *
	 * @return array	Return the formatted data
	 */
	abstract public function parse();

	/**
	 * Verify the data is valid
	 *
	 * @uses self::checkRequiredFields()
	 * @uses self::verifyPaymentNotification()
	 *
	 * @return boolean Returns true on success
	 */
	public function messageSanityCheck()
	{
		$return = false;
		
		if ( $this->checkRequiredFields() ) {
		
			if ( $this->verifyPaymentNotification() ) {
	
				$return = true;
			}
		}
		//Debug::dump($return, eval(DUMP) . "\$return", false);
		
		return $return;
	}

	/**
	 * Verify the data has the required fields
	 *
	 * @return boolean Returns true on success
	 */
	abstract public function checkRequiredFields();

	/**
	 * Verify the payment was made
	 *
	 * @return boolean Returns true on success
	 */
	abstract public function verifyPaymentNotification();

	/**
	 * Generate a response for the merchant provider
	 *
	 * @param array $status The status for the message
	 *
	 * @return mixed Returns a message for the merchant provider
	 */
	abstract public function receiveReturn( $status );

	/**
	 * Push the message to a queue.
	 *
	 * If a queue is not specified, the message will be sent to: unknown_<lower_case_class_name>
	 *
	 * @param string	$queue The queue to send the message
	 */
	public function pushToQueue( $queue = '' )
	{
		$return = true;
		
		$queue = empty( $queue ) ? '/queue/unknown_' . strtolower( get_class( $this ) ) : $queue;

		if ( empty( $this->messageFromPendingQueue ) ) {
			$message = 'There was no message to push to the queue: ' . $queue;
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			
			return false;
		}
	
		// do the queueing - perhaps move out the tracking checking to its own func?
		if ( !$this->stompQueueMessage( $queue, $this->messageFromPendingQueue->body )) {

			$message = 'There was a problem queueing the message to the queue: ' . $queue;
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );

			$message = 'Message: ' . print_r( $this->contribution, true );
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			
			$return = false;
		}

		//Debug::dump($return, eval(DUMP) . "\$return", false);
		return $return;
	}

	/**
	 * Push the message to the pending queue
	 */
	public function pushToPending()
	{

		$return = false;

		//push message to pending queue
		$this->contribution = $this->parse();
		
		if ( $this->messageSanityCheck() ) {
		
			// connect to stomp
			$this->connectStomp();

			$headers = array( 'persistent' => 'true', 'JMSCorrelationID' => $this->getTxId() );
			$message = 'Setting JMSCorrelationID: ' . $this->getTxId();
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
	
			// do the queueing - perhaps move out the tracking checking to its own func?
			if ( !$this->stompQueueMessage( $this->getQueuePending(), json_encode( $this->contribution ), $headers )) {
				$message = 'There was a problem queueing the message to the queue: ' . $this->getQueuePending();
				$this->log( $message, Listener::LOG_LEVEL_DEBUG );
	
				$message = 'Message: ' . print_r( $this->contribution, true );
				$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			}
			else {

				$return = true;
			}
		}

		//Debug::dump($return, eval(DUMP) . "\$return", false);
		return $return;

	}

	/**
	 * Fetch the message from the pending queue
	 *
	 * @return boolean Return true on success.
	 */
	public function fetchFromPending() {
		// define a selector property for pulling a particular msg off the queue
		$properties = array();
		$properties['selector'] = "JMSCorrelationID = '" . $this->getTxId() . "'";

		// pull the message object from the pending queue without completely removing it 
		$message = 'Attempting to pull message from pending queue with JMSCorrelationID: ' . $this->getTxId();
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		$this->messageFromPendingQueue = $this->stompFetchMessage( $this->getQueuePending(), $properties );
		
		if ( $this->messageFromPendingQueue ) {

			$message = 'Pulled message from pending queue: ' . $this->messageFromPendingQueue;
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			
			$return = true;
		}
		else {

			$message = 'FAILED retrieving message from pending queue.';
			$this->log( $message, Listener::LOG_LEVEL_WARN );
			$return = false;
		}
		
		//Debug::dump($return, eval(DUMP) . "\$return", false);
		return $return;
	}

	/**
	 * Push the message to the verified queue
	 *
	 * @return boolean Return true on success.
	 */
	public function pushToVerified() {
		
		$return = true;
		
		// push to verified queue
		if ( !$this->stompQueueMessage( $this->getQueueVerified(), $this->messageFromPendingQueue->body )) {
		
			$message = 'There was a problem queueing the message to the queue: ' . $this->getQueueVerified();
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			
			$message = 'Message: ' . print_r( $this->contribution, true );
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			
			$return = false;
		}
		
		//Debug::dump($return, eval(DUMP) . "\$return", false);
		return $return;
	}
	
	/**
	 * Receive data for processing.
	 * - Send the message to the pending queue
	 * - Fetch from pending (do not remove)
	 * - Verify from pending
	 * - 
	 *
	 * Take the data sent from a PayPal IPN request, verify it against the IPN,
	 * then push the transaction to the queue.  Before verifying the transaction
	 * against the IPN, this will place the transaction originally received in
	 * the pending queue.  If the transaction is verified, it will be removed
	 * from the pending queue and placed in an accepted queue.  If it is not
	 * verified, it will be left in the pending queue for dealing with in some
	 * other fashion.
	 *
	 * @todo
	 * - make this usable by GlobalCollect
	 * - check STATUSID
	 *
	 * @param	array	$data		The data to be saved in a queue.
	 * @param	array	$options	OPTIONAL	Options
	 *
	 * @return	boolean|null	Returns boolean on receipt of data, false if data is empty. 
	 */
	public function receive( $data, $options = array() ) {
		
		$status = false;
		$empty = false;
	 	 
	 	// Make sure we are actually getting something posted to the page.
		if ( empty( $data ) ) {
			
			$message = 'Received an empty message, nothing to verify.';
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			
			return $this->receiveReturn( $status );
		}

		$this->setData( $data );
		
		// Push the message to pending
		if ( $this->pushToPending( $this->getData() ) ) {
			
			// Fetch from pending
			if ($this->fetchFromPending() ) {
				
				// Verify the message we pulled from the pending queue.
				if ( $this->pushToVerified() ) {
			
					// remove from pending
					$this->stompDequeueMessage( $this->messageFromPendingQueue );
					$this->messageFromPendingQueue = null;
					
					$status = true;
				}
			}
		}

		if ( !empty( $this->messageFromPendingQueue )) {
			$this->pushToQueue();
			$this->stompDequeueMessage( $this->messageFromPendingQueue );
		}
		
		return $this->receiveReturn( $status );
	 }

	/**
	 * setActiveMqStompUri
	 *
	 * @param string $uri The activeMQ uri
	 */
	public function setActiveMqStompUri( $uri )
	{
		$this->activeMqStompUri = $uri;
	}

	/**
	 * getActiveMqStompUri
	 *
	 * @return Return the stomp path
	 */
	public function getActiveMqStompUri()
	{
		return $this->activeMqStompUri;
	}

	/**
	 * getAdapterType
	 */
	public function getAdapterType()
	{
		$calledClass = get_called_class();

		return $calledClass::ADAPTER;
	}

	/**
	 * setData
	 */
	public function setData( $data = array() )
	{
		$this->data = empty( $data ) ? array() : (array) $data;
	}

	/**
	 * getData
	 *
	 * @param string $key The key to fetch in the data array
	 * @param boolean $require Require the key to exist, otherwise throw an Exception.
	 *
	 * @return mixed|null Return the data sent to @see $this->receive()
	 */
	public function getData( $key = '', $require = false )
	{
		if ( empty( $key ) ) {

			return $this->data;
		}
		
		if ( !isset( $this->data[ $key ] ) ) {
			
			if ( $require ) {
				$message = 'The required key is not set in data: ' . $key;
				throw new Listener_Exception( $message );
			}
			
			return null;
		}

		return $this->data[ $key ];
		
	}

	/**
	 * setLogFile
	 *
	 * @param string $file The log file
	 */
	public function setLogFile( $file = '' )
	{
		if ( empty( $file ) ) {
			$file = BASE_PATH . '/logs/' . strtolower( $this->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		}

		$directory = dirname( $file );

		// Verify directory exists.
		if ( !is_dir( $directory ) ) {
			$message = 'The directory for the output log does not exist. Please create: ' . $directory;
			throw new Listener_Exception( $message );
		}

		// Verify directory is writable.
		if ( !is_writable( $directory ) ) {
			$message = 'The directory for the output log is not writable. Please chmod +rw: ' . $directory;
			throw new Listener_Exception( $message );
		}

		$this->logFile = $file;
	}

	/**
	 * getLogFile
	 *
	 * @return Return the log file
	 */
	public function getLogFile()
	{
		return $this->logFile;
	}

	/**
	 * setLogLevel
	 *
	 * @param integer $level The log level
	 */
	public function setLogLevel( $level )
	{
		$this->logLevel = (integer) $level;
	}

	/**
	 * getLogLevel
	 *
	 * @return Return the log level
	 */
	public function getLogLevel()
	{
		return $this->logLevel;
	}

	/**
	 * openOutputHandle
	 *
	 * Log files are always opened with the 'a' append flag for writing only.
	 *
	 * @param string $logFile	 OPTIONAL	 The path to a log file
	 */
	public function openOutputHandle( $logFile = '' )
	{
		if ( empty( $logFile ) ) {

			// Create a default log file name
			$this->setLogFile();

			$logFile = $this->getLogFile();
		}

		$this->outputHandle = fopen( $logFile, 'a' );
	}

	/**
	 * getOutputHandle
	 *
	 * @return Return the output handle to the log
	 */
	public function getOutputHandle()
	{
		return $this->outputHandle;
	}

	/**
	 * closeOutputHandle
	 *
	 */
	public function closeOutputHandle()
	{
		if ( $this->hasOutputHandle() ) {
			fclose( $this->getOutputHandle() );
		}
	}

	/**
	 * hasOutputHandle
	 *
	 * @return Return true if @see $this->outputHandle is a resource to a file.
	 */
	public function hasOutputHandle()
	{
		return is_resource( $this->outputHandle );
	}

	/**
	 * Log a message to stdout
	 *
	 * @param $message	The message to log
	 * @param $level	OPTIONAL	The log level. If blank, defaults to @see Listener::LOG_LEVEL_INFO
	 */
	public function log( $message, $level = null )
	{
		// Debug::dump($message, eval(DUMP) . "\$message", false);
		$level = ( is_null( $level ) || $level === false ) ? Listener::LOG_LEVEL_INFO : (integer) $level;

		$return = null;

		// Format message for logging.
		if ( $this->getLogLevel() >= $level ) {
			$return = date( 'c' ) . "\t" . $this->getTxId() . "\t" . $message . "\n";
		}
		// Debug::dump($level, eval(DUMP) . "\$level", false);
		// Debug::dump($return, eval(DUMP) . "\$return", false);
		// Debug::dump($this->hasOutputHandle(), eval(DUMP) . "\$this->hasOutputHandle()", false);

		// If there is a log file set up, write to file, otherwise, send to stdout
		if ( $this->hasOutputHandle() ) {
			fwrite( $this->getOutputHandle(), $return );
		}
		else {
			if ( $this->getLogLevel() >= $level ) {
				//echo "\n" . $message . "\n";
			}
		}

	}

	/**
	 * Erase the log file for the day.
	 */
	public function logTruncate()
	{
		if ( $this->hasOutputHandle() ) {
			ftruncate( $this->getOutputHandle(), 0 );
		}
	}

	/**
	 * Get the contents of a file if it has less than $kilobytes
	 *
	 * @param integer $bytes	 The maximum size of the file in bytes. Default is 1024 bytes.
	 * @return string			 Returns the contents of the file if it is less $bytes. Otherwise, it returns an empty string.
	 */
	public function getLogContents( $bytes = 1024 )
	{
		$bytes = (integer) $bytes;

		$return = '';

		if ( filesize( $this->getLogFile() ) < $bytes ) {
			$return = file_get_contents( $this->getLogFile() );
		}

		return $return;
	}

	/**
	 * connectStomp
	 *
	 * @return boolean Returns true if connected
	 */
	public function connectStomp( $username = '', $password = '' )
	{

		try {

		    $this->getStomp();

			// Debug::dump($this->stomp, eval(DUMP) . "\$this->stomp", true);

			$this->stomp->connect( $username, $password );
			$message = 'Successfully connected to Stomp listener: ' . $this->getActiveMqStompUri();
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			
			return true;

		} catch ( Stomp_Exception $e ) {

			$message = 'Unable to connect with Stomp: ' . $e->getMessage();
			$this->log( $message, Listener::LOG_LEVEL_EMERG );
			
			return false;
		}
	}

	/**
	 * getStomp
	 *
	 */
	public function getStomp()
	{
		// If Stomp is not instatiated, set it up.
		if ( !( $this->stomp instanceof Stomp ) ) {

				$this->setStomp();
		}

		return $this->stomp;
	}

	/**
	 * setStomp
	 *
	 */
	public function setStomp()
	{
		// Require Stomp
		require_once( $this->getStompPath() );

		if ( !class_exists( 'Stomp', false ) ) {
			$message = 'The Stomp class does not exist in: ' . $this->getStompPath();
			throw new Listener_Exception( $message );
		}

		$message = 'Attempting to connect to Stomp listener: ' . $this->getActiveMqStompUri();
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		$this->stomp = new Stomp( $this->getActiveMqStompUri() );
	}

	/**
	 * setStompPath
	 *
	 * @param string $path The path to the stomp script
	 */
	public function setStompPath( $path )
	{
		// Debug::dump($path, eval(DUMP) . "\$path", false);
		if ( !is_file( $path ) ) {
			$message = 'The stomp script does not exist: ' . $path;
			throw new Listener_Exception( $message );
		}

		$this->stompPath = $path;
	}

	/**
	 * getStompPath
	 *
	 * @return Return the stomp path
	 */
	public function getStompPath()
	{
		return $this->stompPath;
	}

	/**
	 * Send a message to the Stomp queue.
	 *
	 * @param $destination string of the destination path for where to send a message
	 * @param $messageDetails string the (formatted) message to send to the queue
	 * @param $properties array of additional Stomp properties
	 * @return bool result from send, false on failure
	 */
	public function stompQueueMessage( $destination, $messageDetails, $properties = array( 'persistent' => 'true' ) ) {

		// persistent is a string. It becomes a header.
		$properties['persistent'] = isset( $properties['persistent'] ) ? (string) $properties['persistent'] : 'true';

		$message = 'Attempting to queue message to: ' . $destination . ' with the txId: ' . $this->getTxId() ;
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );
		$sent = $this->stomp->send( $destination, $messageDetails, $properties );
		$message = 'Result of queuing message: ' . $sent . ' with the txId: ' . $this->getTxId() ;
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		return $sent;
	}

	/**
	 * Remove a message from the Stomp queue.
	 * @param bool $msg
	 */
	public function stompDequeueMessage( $msg ) {

		$message = 'Attempting to remove message from pending.';
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		$this->stomp->ack( $msg );

		$message = 'The verified message was removed from the pending queue: ' .  print_r( json_decode( $msg, true ) );
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );
		
		return true;
	}

	/**
	 * Fetch latest raw message from a queue
	 *
	 * @param $destination string of the destination path from where to fetch a message
	 * @return mixed raw message (Stomp_Frame object) from Stomp client or False if no msg present
	 */
	public function stompFetchMessage( $destination, $properties = array() ) {
		$message = 'Attempting to connect to queue at: ' . $destination;
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		if ( count( $properties ) ) {
			$message = 'With the following properties: ' . print_r( $properties, true );
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
		}

		$this->stomp->subscribe( $destination, $properties );
		$message = 'Attempting to pull queued item.';
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		return $this->stomp->readFrame();
	}

	/**
	 * getQueuePending
	 *
	 * @return Return the queue pending path for ActiveMQ
	 */
	public function getQueuePending()
	{
		return $this->queuePending;
	}

	/**
	 * setQueuePending
	 *
	 * @param string $path The queue pending path for ActiveMQ
	 */
	public function setQueuePending( $path )
	{
		$this->queuePending = $path;
	}

	/**
	 * getQueueVerified
	 *
	 * @return Return the queue pending path for ActiveMQ
	 */
	public function getQueueVerified()
	{
		return $this->queueVerified;
	}

	/**
	 * setQueueVerified
	 *
	 * @param string $path The queue verified path for ActiveMQ
	 */
	public function setQueueVerified( $path )
	{
		$this->queueVerified = $path;
	}

	/**
	 * setTxId
	 */
	public function setTxId( $value = '' )
	{
		$this->txId = empty( $value ) ? time() . '_' . mt_rand() : (string) $value;
	}

	/**
	 * getTxId
	 *
	 * @return Return the transaction ID
	 */
	public function getTxId()
	{
		return $this->txId;
	}
}
