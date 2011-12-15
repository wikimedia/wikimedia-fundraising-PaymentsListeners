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

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'ConnectionTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'ConstructorTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'EscapeTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'LimitTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'QueryTestCase.php';

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'DeleteTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'InsertTestCase.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'UpdateTestCase.php';

/**
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Db_Adapter_Mysqli_AllTests
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
		$suite = new PHPUnit_Framework_TestSuite( 'Queue Handling - Db - Adapter - Mysqli Suite' );

		$suite->addTestSuite( 'Db_Adapter_Mysqli_ConstructorTestCase' );
		$suite->addTestSuite( 'Db_Adapter_Mysqli_ConnectionTestCase' );
		$suite->addTestSuite( 'Db_Adapter_Mysqli_EscapeTestCase' );
		$suite->addTestSuite( 'Db_Adapter_Mysqli_LimitTestCase' );
		$suite->addTestSuite( 'Db_Adapter_Mysqli_QueryTestCase' );
		
		// InsertTestCase should be tested after QueryTestCase.
		$suite->addTestSuite( 'Db_Adapter_Mysqli_DeleteTestCase' );
		$suite->addTestSuite( 'Db_Adapter_Mysqli_InsertTestCase' );
		$suite->addTestSuite( 'Db_Adapter_Mysqli_UpdateTestCase' );

		return $suite;
	}
}
