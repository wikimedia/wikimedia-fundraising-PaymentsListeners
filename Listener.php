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
 * Load the Bootstrap file
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'Bootstrap.php';

/**
 * @see Listener_Exception
 */
require_once 'Listener/Exception.php';

/**
 *
 * @todo
 * - Implement Abstract
 * - Implement GlobalCollect
 *
 * @category	Fundraising
 * @package		Fundraising_QueueHandling
 * @subpackage	Fundraising_QueueHandling_Listener
 */
class Listener
{

	/**
	 * Log level - Emergency: system is unusable
	 */
	const LOG_LEVEL_EMERG	= 0;

	/**
	 * Log level - Alert: action must be taken immediately
	 */
	const LOG_LEVEL_ALERT	= 1;

	/**
	 * Log level - Critical: critical conditions
	 */
	const LOG_LEVEL_CRIT	= 2;

	/**
	 * Log level - Error: error conditions
	 */
	const LOG_LEVEL_ERR		= 3;

	/**
	 * Log level - Warning: warning conditions
	 */
	const LOG_LEVEL_WARN	= 4;

	/**
	 * Log level - Notice: normal but significant condition
	 */
	const LOG_LEVEL_NOTICE	= 5;

	/**
	 * Log level - Informational: informational messages
	 */
	const LOG_LEVEL_INFO	= 6;

	/**
	 * Log level - Debug: debug messages
	 */
	const LOG_LEVEL_DEBUG	= 7;

	/**
	 * Available Adapters to test.
	 *
	 * @var array adapters
	 */
	protected static $adapters = array(
		'GlobalCollect',
		'Paypal',
	);

	/**
	 * Check to see if the adapter is available to load.
	 *
	 * @param string $adapter The adapter to load
	 * @return boolean
	 */
	public static function isAdapter( $adapter )
	{
		// Debug::dump($adapter, eval(DUMP) . "\$adapter", false);
		// Debug::dump(self::$adapters, eval(DUMP) . "self::\$adapters", false);
		return in_array( $adapter, self::$adapters );
	}

	/**
	 * Get the available adapters
	 *
	 * @return boolean
	 */
	public static function getAdapters()
	{
		return self::$adapters;
	}

	/**
	 * Run the main test and load any parameters if needed.
	 *
	 * Adapters available
	 * - GlobalCollect: In development
	 *
	 * @param string $adapter The adapter to load
	 * @param array $parameters The adapter parameters
	 */
	public static function factory( $adapter, $parameters = array() )
	{
		/*
		 * Adapter options must be in the form of an array.
		 */
		if ( !is_array( $parameters ) ) {
			$message = 'Adapter options must be in the form of an array.';
			throw new Listener_Exception( $message );
		}

		/*
		 * You must choose an adapter.
		 */
		if ( !is_string( $adapter ) || empty( $adapter ) ) {
			$message = 'You must choose an adapter.';
			throw new Listener_Exception( $message );
		}

		/*
		 * You must choose a valid adapter.
		 */
		if ( !self::isAdapter( $adapter ) ) {
			$message = 'You must choose a valid adapter. Adapters available: ' . implode( ', ', self::$adapters );
			throw new Listener_Exception( $message );
		}

		// Load the adapter.
		require_once 'Listener/Adapter/' . $adapter . '.php';

		$adapterName = 'Listener_Adapter_' . $adapter;

		/*
		 * Create an instance of the adapter class.
		 * Pass the config to the adapter class constructor.
		 */
		return new $adapterName( $parameters );

	}
}
