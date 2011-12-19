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
 * @author		Jeremy Postlethwaite <jpostlethwaite@wikimedia.org>
 */

require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'TestHelper.php';

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'GlobalCollectPaymentMethodsTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'ConstructorTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'CheckRequiredFieldsTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'DatabaseTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'LimboTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'ReceiveTestCase.php';

/**
 * Listener_Adapter_GlobalCollect_AllTests
 */
class Listener_Adapter_GlobalCollect_AllTests
{

	/**
	 * Run the main test and load any parameters if needed.
	 *
	 */
	public static function main()
	{
		$parameters = array();

		PHPUnit_TextUI_TestRunner::run( self::suite(), $parameters );
	}

	/**
	 * Regular suite
	 *
	 * All tests except those that require output buffering.
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite( 'Queue Handling - Listener - Adapter - GlobalCollect Suite' );

		// Test Listener_Adapter_GlobalCollect_GlobalCollectPaymentMethodsTestCase before GlobalCollect adapter
		$suite->addTestSuite( 'Listener_Adapter_GlobalCollect_GlobalCollectPaymentMethodsTestCase' );
		
		$suite->addTestSuite( 'Listener_Adapter_GlobalCollect_ConstructorTestCase' );

		$suite->addTestSuite( 'Listener_Adapter_GlobalCollect_CheckRequiredFieldsTestCase' );

		$suite->addTestSuite( 'Listener_Adapter_GlobalCollect_DatabaseTestCase' );
		$suite->addTestSuite( 'Listener_Adapter_GlobalCollect_LimboTestCase' );
		$suite->addTestSuite( 'Listener_Adapter_GlobalCollect_ReceiveTestCase' );

		return $suite;
	}
}
