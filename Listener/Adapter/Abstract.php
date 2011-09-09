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
	 * Constructor
	 *
	 * @param array $parameters The adapter parameters
	 * @return boolean
	 */
	public function __construct( $parameters )
	{
		// Extract parameters.
		extract( $parameters );
		
		// Set log level if passed from parameters.
		if ( isset( $logLevel ) ) {
			$this->setLogLevel( $logLevel );
		}
		
		// Set log file if passed from parameters.
		if ( isset( $logFile ) ) {
			$this->setLogFile( $logFile );
		}
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
}
