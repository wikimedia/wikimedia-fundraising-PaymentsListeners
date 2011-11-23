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
 * @see Listener_Exception
 */
require_once 'Listener.php';

/**
 * This file may be linked or duplicated to run the GlobalCollect PSC listener.
 *
 * Logs may will be created in logs/globalcollect
 *
 * They will have the naming format: YYYYMMDD.log.
 *
 */

// The parameters to pass to the factory.
$parameters = array(
	'logLevel' => Listener::LOG_LEVEL_DEBUG,
);

// The adapter to pass to the factory.
$adapter = 'GlobalCollect';

$adapterInstance = Listener::factory( $adapter, $parameters );

// Create a log at logs/globalcollect/YYYYMMDD.log
$adapterInstance->openOutputHandle();

$_POST = isset( $_POST ) ? $_POST : array();

echo $adapterInstance->receive( $_POST );

