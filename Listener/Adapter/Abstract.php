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
	 * @var string activeMqStompUri
	 */
	protected $activeMqStompUri = 'tcp://localhost:61613';

	/**
	 * The log file
	 *
	 * @var string logFile
	 */
	protected $logFile = '';

	/**
	 * The log level
	 *
	 * @see Listener::LOG_LEVEL_QUIET
	 * @see Listener::LOG_LEVEL_INFO
	 * @see Listener::LOG_LEVEL_DEBUG
	 *
	 * @var integer logLevel
	 */
	protected $logLevel = Listener::LOG_LEVEL_QUIET;

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
	 * stompPath
	 *
	 * This is path to Stomp
	 *
	 * @var string stompPath
	 */
	protected $stompPath = '';

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
	 * @return boolean
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
		
		// Set the stomp path if passed from parameters.
		if ( isset( $stompPath ) ) {
			$this->setStompPath( $stompPath );
		}
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
	 * setOutputHandle
	 *
	 * Log files are always opened with the 'a' append flag for writing only.
	 *
	 * @param string $logFile    OPTIONAL    The path to a log file
	 */
	public function setOutputHandle( $logFile = '' )
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
	 * setTxId
	 */
	public function setTxId()
	{
		$this->txId = time() . '_' . mt_rand();
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
