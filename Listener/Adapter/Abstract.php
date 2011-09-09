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
}
