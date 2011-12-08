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
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 */

/**
 * @see Db_Exception
 */
require_once 'Db/Exception.php';

/**
 *
 * @todo
 * - Implement Abstract
 * - Implement GlobalCollect
 *
 */
class Db
{

	/**
	 * Available Adapters to test.
	 *
	 * @var array adapters
	 */
	protected static $adapters = array(
		'Mysqli',
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
			throw new Db_Exception( $message );
		}

		/*
		 * You must choose an adapter.
		 */
		if ( !is_string( $adapter ) || empty( $adapter ) ) {
			$message = 'You must choose an adapter.';
			throw new Db_Exception( $message );
		}
		
		/*
		 * You must choose a valid adapter.
		 */
		if ( !self::isAdapter( $adapter ) ) {
			$message = 'You must choose a valid adapter. Adapters available: ' . implode( ', ', self::$adapters );
			throw new Db_Exception( $message );
		}

		// Load the adapter.
		require_once 'Db/Adapter/' . $adapter . '.php';

		$adapterName = 'Db_Adapter_' . $adapter;

		/*
		 * Create an instance of the adapter class.
		 * Pass the config to the adapter class constructor.
		 */
		return new $adapterName( $parameters );

	}
}
