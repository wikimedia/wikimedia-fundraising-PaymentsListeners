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
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 * @since		r462
 * @author		Jeremy Postlethwaite <jpostlethwaite@wikimedia.org>
 */

 /**
 * Timer start constant
 *
 * @ignore
 */
$GLOBALS['TIMER_START'] = microtime( true );

/**
 * Timer start constant
 *
 * @ignore
 */
$GLOBALS['wgCommandLineMode'] = true;

/**
 * Timer start constant
 *
 * @ignore
 */
if ( !defined( 'TIMER_START' ) ) {
	define( 'TIMER_START', $GLOBALS['TIMER_START'] );
}

/**
 * Base path
 *
 * @ignore
 */
if ( !defined( 'BASE_PATH' ) ) {
	define( 'BASE_PATH', realpath( dirname( __FILE__ ) ) );
}

/**
 * Library path
 *
 * @ignore
 */
if ( !defined( 'LIBRARY_PATH' ) ) {
	define( 'LIBRARY_PATH', BASE_PATH . '/library' );
}

// Add LIBRARY_PATH and BASE_PATH directory to the include path
set_include_path( LIBRARY_PATH . PATH_SEPARATOR . get_include_path() );
set_include_path( BASE_PATH . PATH_SEPARATOR . get_include_path() );

/**
 * The application environment
 * @ignore
 */
if ( !isset( $_SERVER ) ) {
	$_SERVER = array();
}

$_SERVER['APPLICATION_ENVIRONMENT'] = 'unittesting';

/**
 * The application environment
 */
if ( !defined( 'APPLICATION_ENVIRONMENT' ) ) {
	define( 'APPLICATION_ENVIRONMENT', $_SERVER['APPLICATION_ENVIRONMENT'] );
}

/**
 * @see QueueHandling_Exception
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'Exception.php';

/**
 * @see Debug
 */
require_once 'Debug.php';

