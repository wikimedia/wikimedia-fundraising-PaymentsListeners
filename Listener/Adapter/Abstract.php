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
	 * @var string $activeMqStompUri
	 */
	protected $activeMqStompUri = 'tcp://localhost:61613';

	/**
	 * The contribution
	 *
	 * @var array $contribution
	 */
	protected $contribution = array();

	/**
	 * The data
	 *
	 * @var array $data
	 */
	protected $data = array();

	/**
	 * The adapter to the database
	 *
	 * @var Db_Adapter_Abstract $db
	 */
	protected $db;

	/**
	 * If the message is found in the database it will be set to true.
	 *
	 * If the message is not found in the database it will be set to false.
	 *
	 * The value will be null if it has not been checked 
	 *
	 * @var null|boolean $inDatabase
	 */
	protected $inDatabase = null;

	/**
	 * If the message is found in the limbo queue it will be set to true.
	 *
	 * If the message is not found in the limbo queue it will be set to false.
	 *
	 * The value will be null if it has not been checked 
	 *
	 * @var null|boolean $inLimbo
	 */
	protected $inLimbo = null;

	/**
	 * The limbo ID
	 *
	 * @var string $limboId
	 */
	protected $limboId = '';

	/**
	 * The limbo ID Name
	 *
	 * @var string $limboIdName
	 */
	protected $limboIdName = '';

	/**
	 * The log file
	 *
	 * @var string $logFile
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
	 * @var integer $logLevel
	 */
	protected $logLevel = Listener::LOG_LEVEL_DEBUG;

	/**
	 * messageFromPendingQueue
	 *
	 * This is message fetched from the pending queue
	 *
	 * @var StompFrame $messageFromPendingQueue
	 */
	protected $messageFromPendingQueue;

	/**
	 * messageFromLimboQueue
	 *
	 * This is message fetched from the limbo queue
	 *
	 * @var StompFrame $messageFromLimboQueue
	 */
	protected $messageFromLimboQueue;

	/**
	 * outputHandle
	 *
	 * This is a resource created by fopen.
	 *
	 * @var resource $outputHandle
	 */
	protected $outputHandle;

	/**
	 * pullFromDatabase
	 *
	 * Pull from the database.
	 *
	 * @var boolean $pullFromDatabase
	 */
	protected $pullFromDatabase = false;

	/**
	 * pullFromLimbo
	 *
	 * Pull from the limbo queue
	 *
	 * @var boolean $pullFromLimbo
	 */
	protected $pullFromLimbo = false;

	/**
	 * queueLimbo
	 *
	 * This is path to limbo queue.
	 *
	 * Messages get sent to the limbo queue when they are created by the form.
	 * They will be removed from the limbo queue when a listener post has 
	 * identified the a message by order id.
	 *
	 *
	 * @var string $queueLimbo
	 */
	protected $queueLimbo = '/queue/limbo';

	/**
	 * queuePending
	 *
	 * This is path to pending queue
	 *
	 * @var string $queuePending
	 */
	protected $queuePending = '/queue/pending';

	/**
	 * queueVerified
	 *
	 * This is path to verified queue
	 *
	 * @var string $queueVerified
	 */
	protected $queueVerified = '/queue/verified';

	/**
	 * row
	 *
	 * A row from the database. This should have a limboId.
	 *
	 * @var array $row
	 */
	protected $row = array();

	/**
	 * settings
	 *
	 * Settings from the configuration file
	 *
	 * @var array $settings
	 */
	protected $settings = array();

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
	 * @var string $stompPath
	 */
	protected $stompPath = 'Stomp.php';

	/**
	 * txId
	 *
	 * This is the transaction id
	 *
	 * @var string $txId
	 */
	protected $txId = '';

	/**
	 * Constructor
	 *
	 * @param array $parameters The adapter parameters
	 *
	 * $parameters
	 * - activeMqStompUri:	Change the URI for ActiveMQ			DEFAULT => tcp://localhost:61613
	 * - log:				Enable logging 						DEFAULT => true
	 * - logFile:			Set the log file path 				DEFAULT => BASE_PATH . '/logs/' . strtolower( $this->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log' - Setting this enables logging
	 * - logLevel:			Set the log level					DEFAULT => Listener::LOG_LEVEL_DEBUG - Setting this enables logging
	 * - settings:			Set the path to the settings file	DEFAULT => settings.ini - This should be in queue_handling/ - You may also specify an absolute path to a file.
	 * - stompPath:			Set the path to Stomp 				DEFAULT => Stomp.php - This should be in queue_handling/library/
	 *
	 */
	public function __construct( $parameters = array() )
	{

		// Create transaction id
		$this->setTxId();

		// Get the parameters
		$activeMqStompUri	= isset( $parameters['activeMqStompUri'] )	? $parameters['activeMqStompUri']	: null;
		$log				= isset( $parameters['log'] )				? (boolean) $parameters['log']		: true;
		$logFile			= isset( $parameters['logFile'] )			? $parameters['logFile']			: null;
		$logLevel			= isset( $parameters['logLevel'] )			? $parameters['logLevel']			: null;
		$settings			= isset( $parameters['settings'] )			? $parameters['settings']			: null;
		$stompPath			= isset( $parameters['stompPath'] )			? $parameters['stompPath']			: null;
		
		// Set the stomp path if passed from parameters.
		if ( isset( $activeMqStompUri ) ) {
			$this->setActiveMqStompUri( $activeMqStompUri );
		}

		// Set log level if passed from parameters.
		if ( isset( $logLevel ) ) {
			$this->setLogLevel( $logLevel );
			$log = true;
		}

		// Set log file if passed from parameters.
		if ( isset( $logFile ) ) {
			$this->setLogFile( $logFile );
			$log = true;
		}

		if ( $log ) {
			$this->openOutputHandle();
		
			$message = 'Loading ' . $this->getAdapterType() . ' processor with log level: ' . $this->getLogLevel();
			$this->log( $message );
		}

		// Set the stomp path if passed from parameters.
		if ( isset( $stompPath ) ) {
			$this->setStompPath( $stompPath );
		}
		
		if ( isset( $settings ) ) {
			$this->setSettings( $settings );
		}
		else {
			$message = 'Settings are not being loaded. A configuration file must be specified.';
			$this->log( $message, Listener::LOG_LEVEL_ERR );
			throw new Listener_Exception( $message );
		}
		
		$this->init();
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
	 * Initialize the class
	 *
	 * init() is called at the end of the constructor to allow automatic settings for adapters.
	 */
	abstract protected function init();

	/**
	 * Parse the data and format for Contribution Tracking
	 *
	 * @return array	Return the formatted data
	 */
	abstract public function parse();

	/**
	 * Get the decision on whether or not the message will undergo further
	 * processing.
	 *
	 * This method provides the adapter with the ability handle messages.
	 *
	 * @return boolean	Returns true if the message can be handled by @see Listener_Adapter_Abstract::receive
	 */
	abstract public function getProcessDecision();

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
	 * Push the message to the pending queue
	 *
	 * @param mixed		$activeMqMessage	The message to be sent to the queue 
	 * @param string	$queue				The destination queue for the message 
	 * @param string	$id					This will set JMSCorrelationID. If an empty value is passed, $this->getTxId() will be used instead.
	 * @param array		$options			Optional settings
	 * - $json:	(boolean)	 By default, messages will be encoded with json.
	 *
	 * @return boolean	Returns true on success
	 */
	public function pushToQueueWithJmsCorrelationId( $activeMqMessage, $queue, $id, $options = array() )
	{

		// Make sure the id is not empty
		$id = empty( $id ) ? $this->getTxId() : $id;
		
		// Encode messages with json by default
		$json = isset( $options['json'] ) ? (boolean) $options['json'] : true;
		$activeMqMessage = $json ? json_encode( $activeMqMessage ) : $activeMqMessage;
		
		// The return value
		$return = false;

		// connect to stomp
		$this->connectStomp();

		$headers = array( 'persistent' => 'true', 'JMSCorrelationID' => $id );
		$message = 'Setting JMSCorrelationID: ' . $id;
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		// Queue the message
		$return = $this->stompQueueMessage( $queue, $activeMqMessage, $headers );
		
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
			$return = $this->stompQueueMessage( $this->getQueuePending(), json_encode( $this->contribution ), $headers );
		}

		return $return;

	}

	/**
	 * Fetch the message from the database queue
	 *
	 * @param string	$orderId 	The order_id in the table `queue2civicrm_limbo`
	 * @param array		$options	Optional settings
	 * - $dequeue:	(boolean)	 By default, messages will not be dequeued.
	 *
	 * @return boolean Return true on success.
	 */
	public function fetchFromDatabaseByOrderId( $orderId = '', $options = array() ) {

		$return = false;
		
		$orderId = empty( $orderId ) ? $this->getData( $this->getLimboIdName(), true) : $orderId;
		
		// An $orderId must be set to search.
		if ( empty( $orderId ) ) {
			$message = $this->getLimboIdName() . ' must not be empty.';
			$this->log( $message, Listener::LOG_LEVEL_EMERG );
			throw new Listener_Exception( $message );
		}
		
		$this->getDb();
		$message = 'Fetching by order_id from the database.';
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		$query = "SELECT * FROM `queue2civicrm_limbo` where `order_id` = '?'";
		//Debug::dump($orderId, eval(DUMP) . __FUNCTION__ . PN . _ . "\$orderId");
		//Debug::dump($query, eval(DUMP) . __FUNCTION__ . PN . _ . "\$query");
		$this->log( $query . ' -> ' . $orderId, Listener::LOG_LEVEL_DEBUG );
		$this->db->query( $this->db->quoteInto( $query, $orderId ) );
		
		$row = $this->db->fetch();
		$this->log( 'Query result: ' . print_r( $row, true ), Listener::LOG_LEVEL_DEBUG );
		
		if ( is_array( $row ) && isset( $row['order_id'] ) ) {
		
			// Return true if the order_id matches $orderId
			$return = ( $row['order_id'] == $orderId );
		}

		$message = 'The order_id ' . $orderId . ' was';
		$message .= $return ? '' : ' not';
		$message .= ' found in `queue2civicrm_limbo`.';
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );
		
		return $return;
	}

	/**
	 * Fetch the message from the limbo queue
	 *
	 * @param string	$limboId 	The JMSCorrelationID of the message in the limbo queue
	 * @param array		$options	Optional settings
	 * - $dequeue:	(boolean)	 By default, messages will not be dequeued.
	 *
	 * @return boolean Return true on success.
	 */
	public function fetchFromLimbo( $limboId = '', $options = array() ) {

		$dequeue = empty( $options['dequeue'] ) ? false : (boolean) $options['dequeue'];
		$return = false;
		
		if ( empty( $limboId ) ) {
			
			$orderId = $this->getData( $this->getLimboIdName(), true );
		
			if ( empty( $orderId ) ) {
				$message = 'The order_id must be set.';
				throw new Listener_Exception( $message );
			}
			
			$limboId = $this->getAdapterTypeLowerCase() . '-' . $orderId;
			$message = 'Fetching limbo Id from data.';
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
		}

		// connect to stomp
		$this->connectStomp();
		
		// define a selector property for pulling a particular msg off the queue
		$properties = array();
		$properties['selector'] = "JMSCorrelationID = '" . $limboId . "'";

		// pull the message object from the pending queue without completely removing it 
		$message = 'Attempting to pull message from pending queue with JMSCorrelationID: ' . $limboId;
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		$this->messageFromLimboQueue = $this->stompFetchMessage( $this->getQueueLimbo(), $properties );
		
		if ( $this->messageFromLimboQueue ) {

			$message = 'Pulled message from limbo queue: ' . $this->messageFromLimboQueue;
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );

			if ( $dequeue ) {
				// remove from limbo
				$this->stompDequeueMessage( $this->messageFromLimboQueue );
				$this->messageFromLimboQueue = null;
			}

			$return = true;
		}
		else {

			$message = 'Message does not exist in limbo queue.';
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
			$return = false;
		}
		
		return $return;
	}

	/**
	 * Fetch the message from the limbo queue and remove it
	 *
	 * @param string	$limboId 	The JMSCorrelationID of the message in the limbo queue
	 * @param array		$options	@see Listener_Adapter_Abstract::fetchFromLimbo
	 *
	 * @return boolean Return true on success.
	 */
	public function fetchFromLimboAndDequeue( $orderId = '', $options = array() ) {
		
		$options['dequeue'] = true;
		
		return $this->fetchFromLimbo( $orderId, $options );
	}
	
	/**
	 * Fetch the message from the pending queue
	 * @param string	$limboId 	The JMSCorrelationID of the message in the limbo queue
	 * @param array		$options	Optional settings
	 * - $dequeue:	(boolean)	 By default, messages will not be dequeued.
	 *
	 * @return boolean Return true on success.
	 */
	public function fetchFromPending( $limboId = '', $options = array() ) {
		
		$dequeue = empty( $options['dequeue'] ) ? false : (boolean) $options['dequeue'];
		$limboId = empty( $limboId ) ? $this->getTxId() : $limboId;
		// define a selector property for pulling a particular msg off the queue
		$properties = array();
		$properties['selector'] = "JMSCorrelationID = '" . $limboId . "'";

		// pull the message object from the pending queue without completely removing it 
		$message = 'Attempting to pull message from pending queue with JMSCorrelationID: ' . $this->getTxId();
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		$this->messageFromPendingQueue = $this->stompFetchMessage( $this->getQueuePending(), $properties );
		//Debug::dump($this->messageFromPendingQueue, eval(DUMP) . "\$this->messageFromPendingQueue", true);
		
		if ( $this->messageFromPendingQueue ) {

			$message = 'Pulled message from pending queue: ' . $this->messageFromPendingQueue;
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );

			if ( $dequeue ) {
				// remove from limbo
				$this->stompDequeueMessage( $this->messageFromPendingQueue );
				$this->messageFromPendingQueue = null;
			}
			
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
	 * Fetch the message from the pending queue and remove it
	 *
	 * @param string	$limboId 	The JMSCorrelationID of the message in the limbo queue
	 * @param array		$options	@see Listener_Adapter_Abstract::fetchFromLimbo
	 *
	 * @return boolean Return true on success.
	 */
	public function fetchFromPendingAndDequeue( $limboId = '', $options = array() ) {
		
		$options['dequeue'] = true;
		
		return $this->fetchFromPending( $limboId, $options );
	}

	/**
	 * Push the message to the verified queue
	 *
	 * @return boolean Return true on success.
	 */
	public function pushToVerified() {
		
		// push to verified queue
		$return = $this->stompQueueMessage( $this->getQueueVerified(), $this->messageFromPendingQueue->body );
		
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
		//Debug::dump($data, eval(DUMP) . "\$data", false);
		try {
		
			$status = false;
			$empty = false;
			// Make sure we are actually getting something posted to the page.
			if ( empty( $data ) ) {
				
				$message = 'Received an empty message, nothing to verify.';
				$this->log( $message, Listener::LOG_LEVEL_DEBUG );
				
				return $this->receiveReturn( $status );
			}
	
			$this->setData( $data );
	
			// Log the message
			$message = 'Received a message: ' . print_r( $this->getData(), true );
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
	
			$proceedWithProcessing = $this->getProcessDecision();
			
			if ( !$proceedWithProcessing ) {
	
				// Tell the provider we received the message with a status of true
				$status = true;
				$message = 'Message with [' . $this->getLimboIdName() . ' = ' . $this->getData( $this->getLimboIdName() ) . '] will not be processed.';
				$this->log( $message, Listener::LOG_LEVEL_DEBUG );
	
				return $this->receiveReturn( $status );
			}
			
			$inLimbo = null;
			$inDatabase = null;
			
			if ( $this->getPullFromLimbo() ) {
	
				$inLimbo = $this->fetchFromLimbo();
			}
			
			$this->setInLimbo( $inLimbo );
			
			if ( $this->getPullFromDatabase() ) {
	
				if ( !$inLimbo ) {
					$inDatabase = $this->fetchFromDatabaseByOrderId();
				}
			}
			
			$this->setInDatabase( $inDatabase );
			
			$exists = ( $this->getInLimbo() || $this->getInDatabase() ) ? true : false;
			//Debug::dump($exists, eval(DUMP) . "\$exists");
			
			if ( !$exists ) {
	
				// Tell the provider we received the message with a status of true
				// Should send an alert about this?
				$status = true;
				$message = 'Message with [' . $this->getLimboIdName() . ' = ' . $this->getData( $this->getLimboIdName() ) . '] does not be exist in limbo or the database.';
				$this->log( $message, Listener::LOG_LEVEL_EMERG );
			}
			
			// We will only push to verified
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
			
		} catch ( Listener_Exception $e ) {

			$message = $e->getMessage();
			$this->log( $message, Listener::LOG_LEVEL_EMERG );
			
			throw new Listener_Exception( $message );
			
		} catch ( Exception $e ) {

			$message = 'Unknown error: ' . $e->getMessage();
			$this->log( $message, Listener::LOG_LEVEL_EMERG );
			
			$status = false;
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
	 *
	 * @return	string	Return the adapter type
	 */
	public function getAdapterType()
	{
		$calledClass = get_called_class();

		return $calledClass::ADAPTER;
	}

	/**
	 * getAdapterTypeLowerCase
	 *
	 * @return	string	Return the adapter type in lowercase.
	 */
	public function getAdapterTypeLowerCase()
	{
		return strtolower( $this->getAdapterType() );
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
	 * @param string	$key		The key to fetch in the data array
	 * @param boolean	$require	Require the key to exist, otherwise throw an Exception.
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
	 * getDb
	 *
	 */
	public function getDb()
	{
		$settings = $this->getSettings('db');
		
		if ( !isset( $settings ) ) {
			$message = 'Database adapter parameters must be setup in the settings configuration file.';
			throw new Listener_Exception( $message );
		}
		
		// If database adapter is not instantiated, set it up.
		if ( empty( $this->db ) ) {

			// The adapter to pass to the factory.
			$adapter = isset( $settings['adapter'] ) ? $settings['adapter'] : '';
			
			$this->db = Db::factory( $adapter, $settings );
		}

		return $this->db;
	}

	/**
	 * getInDatabase
	 *
	 * @return boolean|null
	 */
	public function getInDatabase()
	{
		return $this->inDatabase;
	}

	/**
	 * setInDatabase
	 *
	 * @param boolean $inDatabase
	 */
	public function setInDatabase( $inDatabase )
	{
		$this->inDatabase = (boolean) $inDatabase;
	}

	/**
	 * getInLimbo
	 *
	 * @return boolean|null
	 */
	public function getInLimbo()
	{
		return $this->inLimbo;
	}

	/**
	 * setInLimbo
	 *
	 * @param boolean $inLimbo
	 */
	public function setInLimbo( $inLimbo )
	{
		$this->inLimbo = (boolean) $inLimbo;
	}

	/**
	 * getLimboId
	 *
	 * @return string Returns the limbo ID
	 */
	public function getLimboId()
	{
		return $this->limboId;
	}

	/**
	 * setLimboId
	 *
	 * @param string $value The value of the id.
	 */
	public function setLimboId( $value )
	{
		$this->limboId = $value;
	}

	/**
	 * getLimboIdName
	 *
	 * @return string Returns the name of the limbo ID
	 */
	public function getLimboIdName()
	{
		return $this->limboIdName;
	}

	/**
	 * setLimboIdName
	 *
	 * @param string $limboIdName The limbo ID name
	 */
	public function setLimboIdName( $limboIdName )
	{
		$this->limboIdName = $limboIdName;
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
		if ( empty( $this->logFile ) ) {

			// Create a default log file name
			$this->setLogFile();
		}
		
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
	 */
	public function openOutputHandle()
	{
		$this->outputHandle = fopen( $this->getLogFile(), 'a' );
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
		$level = ( is_null( $level ) || $level === false ) ? Listener::LOG_LEVEL_INFO : (integer) $level;

		$return = null;

		// Format message for logging.
		if ( $this->getLogLevel() >= $level ) {
			$return = date( 'c' ) . "\t" . $this->getTxId() . "\t" . $message . "\n";
		}

		// If there is a log file set up, write to file, otherwise, send to stdout
		if ( $this->hasOutputHandle() ) {
			fwrite( $this->getOutputHandle(), $return );
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
	 * setSettings
	 *
	 * Sets settings for the adapter:
	 * - database adapter parameters
	 *
	 * @param string $file The settings file
	 */
	public function setSettings( $file )
	{
		if ( !is_file( $file ) ) {
			$message = 'File does not exist for Listener: ' . $file;
			$this->log( $message, Listener::LOG_LEVEL_EMERG );
			throw new Listener_Exception( $message );
		}

		// Parse the settings file
		$this->settings = parse_ini_file( $file, true );
		//Debug::dump($this->settings, eval(DUMP) . "\$this->settings", false);
		
		// Check to see if we have database adapter settings.
		if ( !isset( $this->settings['db'] ) ) {
			
			// Database adapter settings do not exist
			$pullFromDatabase = false;
		}
		elseif ( !isset( $this->settings['db']['enable'] ) || empty( $this->settings['db']['enable'] ) ) {

			// Database adapter is not enabled
			$pullFromDatabase = false;
		}
		else {
			$pullFromDatabase = true;
		}
		
		//Debug::dump($pullFromDatabase, eval(DUMP) . "\$pullFromDatabase", false);
		$this->setPullFromDatabase( $pullFromDatabase );
		
		if ( $pullFromDatabase ) {

			/**
			 * @see Listener_Adapter_Abstract
			 */
			require_once 'Db.php';
			
			unset( $this->settings['db']['enable'] );
		}
		else {
			$this->settings['db'] = array();
			$message = 'No connections will be made to the database.';
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
		}
	}

	/**
	 * getSettings
	 *
	 * @param string	$key		The key to fetch in the data array
	 * @param boolean	$require	Require the key to exist, otherwise throw an Exception.
	 *
	 * @return mixed|null Return the settings from the configuration file.
	 */
	public function getSettings( $key = '', $require = false )
	{
		if ( empty( $key ) ) {

			return $this->settings;
		}
		
		if ( !isset( $this->settings[ $key ] ) ) {
			
			if ( $require ) {
				$message = 'The required key is not set in settings: ' . $key;
				throw new Listener_Exception( $message );
			}
			
			return null;
		}

		return $this->settings[ $key ];
	}

	/**
	 * resetSettings
	 *
	 */
	public function resetSettings()
	{
		$this->settings = array();
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
		
		if ( !$sent ) {
			$message = 'Unable to send message to queue: ' . $destination . ' - with message details: ' . $messageDetails . ' - and properties: ' . print_r( $properties, true );
			$this->log( $message, Listener::LOG_LEVEL_ERR );
			throw new Listener_Exception( $message );
		}

		return $sent;
	}

	/**
	 * Remove a message from the Stomp queue.
	 *
	 * @param Stomp_Frame  $msg
	 */
	public function stompDequeueMessage( $msg ) {
		
		if ( !( $msg instanceof Stomp_Frame ) ) {
			$message = 'The messages is not an instance of Stomp_Frame: ' . print_r( $msg, true );
			$this->log( $message, Listener::LOG_LEVEL_ERR );
			throw new Listener_Exception( $message );
		}

		$message = 'Attempting to remove message from queue.';
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		$this->stomp->ack( $msg );

		$message = 'The message was removed from the queue: ' .  print_r( json_decode( $msg, true ), true );
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

		$properties['ack'] = 'client';
		
		if ( count( $properties ) ) {
			$message = 'With the following properties: ' . print_r( $properties, true );
			$this->log( $message, Listener::LOG_LEVEL_DEBUG );
		}

		$this->connectStomp();

 		$this->stomp->subscribe( $destination, $properties );
		$message = 'Attempting to pull queued item.';
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );

		return $this->stomp->readFrame();
	}

	/**
	 * getPullFromDatabase
	 *
	 * @return boolean Returns true if the script needs to pull from the database.
	 */
	public function getPullFromDatabase()
	{
		return $this->pullFromDatabase;
	}

	/**
	 * setPullFromDatabase
	 *
	 * @param boolean $pull Setting $pull to true will enable pulling from the database.
	 */
	public function setPullFromDatabase( $pull )
	{
		$this->pullFromDatabase = (boolean) $pull;
	}

	/**
	 * getPullFromLimbo
	 *
	 * @return boolean Returns true if the script needs to pull from the limbo queue.
	 */
	public function getPullFromLimbo()
	{
		return $this->pullFromLimbo;
	}

	/**
	 * setPullFromLimbo
	 *
	 * @param boolean $pull Setting $pull to true will enable pulling from the limbo queue.
	 */
	public function setPullFromLimbo( $pull )
	{
		$this->pullFromLimbo = (boolean) $pull;
	}

	/**
	 * getQueueLimbo
	 *
	 * @return Return the queue limbo path for ActiveMQ
	 */
	public function getQueueLimbo()
	{
		return $this->queueLimbo;
	}

	/**
	 * setQueueLimbo
	 *
	 * @param string $path The queue limbo path for ActiveMQ
	 */
	public function setQueueLimbo( $path )
	{
		$this->queueLimbo = $path;
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
