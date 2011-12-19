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

/**
 * @see QueueHandlingTestCase
 */
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		Db
 * @group		Mysqli
 *
 * Db_Adapter_Mysqli_InsertTestCase
 */
class Db_Adapter_Mysqli_InsertTestCase extends QueueHandlingTestCase
{

	/**
	 * testDeleteThenInsertAndFetchTheRecord
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::delete
	 * @covers Db_Adapter_Abstract::insert
	 * @covers Db_Adapter_Abstract::query
	 * @covers Db_Adapter_Abstract::getLastInsertId
	 * @covers Db_Adapter_Abstract::quoteInto
	 * @covers Db_Adapter_Mysqli::setLastInsertId
	 * @covers Db_Adapter_Mysqli::getErrorCode
	 * @covers Db_Adapter_Mysqli::lastInsertId
	 * @covers Db_Adapter_Mysqli::fetch
	 * @covers Db_Adapter_Abstract::getResult
	 * @covers Db_Expression::__construct
	 * @covers Db_Expression::__toString
	 *
	 */
	public function testDeleteThenInsertAndFetchTheRecord() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
			'port'		=> TESTS_DB_ADAPTER_PORT,
			'socket'	=> TESTS_DB_ADAPTER_SOCKET,
			'flags'		=> MYSQLI_CLIENT_INTERACTIVE,
		);

		// The adapter to pass to the factory.
		$adapter = 'Mysqli';

		$adapterInstance = Db::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Db_Adapter_Mysqli', $adapterInstance );
		$this->assertInstanceOf( 'mysqli', $adapterInstance->getConnection() );

		//$insert = 'SHOW DATABASES LIKE ' . $adapterInstance->escape( TESTS_DB_ADAPTER_DATABASE_FOR_TESTING );
		$table = 'queue2civicrm_limbo';


		$id = 1;
		$key = 'queue2civicrm_limbo_id';
		$orderId = 1;
		
		// Delete the record if it exists
		$value = $adapterInstance->delete( $table, $key, $id );

		
		$data = array(
			'queue2civicrm_limbo_id'		=> $id,
			'contribution_tracking_id'	=> new Db_Expression(1),
			'order_id'					=> $orderId,
			'timestamp'					=> time(),
			'data'						=> 'Just some data that should be in json format.',
			'gateway'					=> strtolower( TESTS_LISTENER_ADAPTER_DEFAULT ),
			'payment_method'			=> 'rtbt',
			'payment_submethod'			=> 'rtbt_ideal',
		);
		
		$value = $adapterInstance->insert( $table, $data );
		//Debug::dump($value, eval(DUMP) . __FUNCTION__ . PN . _ . "\$value");

		$query = "SELECT * FROM `queue2civicrm_limbo` where `order_id` = '?'";
		$adapterInstance->query( $adapterInstance->quoteInto( $query, $orderId ) );

		$row = $adapterInstance->fetch();
		//Debug::dump($row, eval(DUMP) . __FUNCTION__ . PN . _ . "\$row");
		
		$this->assertArrayHasKey( 'queue2civicrm_limbo_id', $row );
		
		// $id will be a string from the database.
		$this->assertSame( (string) $id, $row['queue2civicrm_limbo_id'] );
		//Debug::puke($adapterInstance, eval(DUMP) . __FUNCTION__ . PN . _ . "\$adapterInstance");
	}

	/**
	 * testInsertWithDuplicatePrimaryKeyAndWatchForException
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::insert
	 * @covers Db_Adapter_Abstract::query
	 * @covers Db_Adapter_Mysqli::getErrorCode
	 * @covers Db_Adapter_Mysqli::getErrorMessage
	 * @covers Db_Expression::__construct
	 * @covers Db_Expression::__toString
	 *
	 */
	public function testInsertWithDuplicatePrimaryKeyAndWatchForException() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
			'port'		=> TESTS_DB_ADAPTER_PORT,
			'socket'	=> TESTS_DB_ADAPTER_SOCKET,
			'flags'		=> MYSQLI_CLIENT_INTERACTIVE,
		);

		// The adapter to pass to the factory.
		$adapter = 'Mysqli';

		$adapterInstance = Db::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Db_Adapter_Mysqli', $adapterInstance );
		$this->assertInstanceOf( 'mysqli', $adapterInstance->getConnection() );

		$message = "Duplicate entry '1' for key 'PRIMARY'";
		$this->setExpectedException( 'Db_Exception', $message );
		
		$table = 'queue2civicrm_limbo';


		$id = 1;
		$key = 'queue2civicrm_limbo_id';

		
		$data = array(
			'queue2civicrm_limbo_id'		=> new Db_Expression(1),
			'contribution_tracking_id'	=> new Db_Expression(1),
			'order_id'					=> new Db_Expression(1),
			'timestamp'					=> time(),
			'data'						=> 'Just some data that should be in json format.',
			'gateway'					=> strtolower( TESTS_LISTENER_ADAPTER_DEFAULT ),
			'payment_method'			=> 'rtbt',
			'payment_submethod'			=> 'rtbt_ideal',
		);
		
		$value = $adapterInstance->insert( $table, $data );
		//Debug::puke($adapterInstance, eval(DUMP) . __FUNCTION__ . PN . _ . "\$adapterInstance");
		//$adapterInstance->fetchAll();
	}

	/**
	 * testInsertWithNoDataToGenerateAnException
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::insert
	 * @covers Db_Adapter_Abstract::query
	 * @covers Db_Adapter_Abstract::getLastInsertId
	 * @covers Db_Adapter_Abstract::quoteInto
	 * @covers Db_Adapter_Mysqli::setLastInsertId
	 * @covers Db_Adapter_Mysqli::getErrorCode
	 * @covers Db_Adapter_Mysqli::lastInsertId
	 * @covers Db_Adapter_Mysqli::fetch
	 * @covers Db_Adapter_Abstract::getResult
	 * @covers Db_Expression::__construct
	 * @covers Db_Expression::__toString
	 *
	 */
	public function testInsertWithNoDataToGenerateAnException() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
			'port'		=> TESTS_DB_ADAPTER_PORT,
			'socket'	=> TESTS_DB_ADAPTER_SOCKET,
			'flags'		=> MYSQLI_CLIENT_INTERACTIVE,
		);

		// The adapter to pass to the factory.
		$adapter = 'Mysqli';

		$adapterInstance = Db::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Db_Adapter_Mysqli', $adapterInstance );
		$this->assertInstanceOf( 'mysqli', $adapterInstance->getConnection() );

		//$insert = 'SHOW DATABASES LIKE ' . $adapterInstance->escape( TESTS_DB_ADAPTER_DATABASE_FOR_TESTING );
		$table = 'queue2civicrm_limbo';

		$data = array();
		$message = 'No fields set for insertion on table: ' . $table;
		$this->setExpectedException( 'Db_Exception', $message );
		
		$value = $adapterInstance->insert( $table, $data );
		//Debug::dump($value, eval(DUMP) . __FUNCTION__ . PN . _ . "\$value");
	}

	/**
	 * testInsertWithoutSpecifyingATableAndGenerateAnException
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::insert
	 *
	 */
	public function testInsertWithoutSpecifyingATableAndGenerateAnException() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
			'port'		=> TESTS_DB_ADAPTER_PORT,
			'socket'	=> TESTS_DB_ADAPTER_SOCKET,
			'flags'		=> MYSQLI_CLIENT_INTERACTIVE,
		);

		// The adapter to pass to the factory.
		$adapter = 'Mysqli';

		$adapterInstance = Db::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Db_Adapter_Mysqli', $adapterInstance );
		$this->assertInstanceOf( 'mysqli', $adapterInstance->getConnection() );

		$table = '';
		$data = array();

		$message = '$table cannot be empty.';
		$this->setExpectedException( 'Db_Exception', $message );
		
		// Attempt to insert
		$adapterInstance->insert( $table, $data );
	}
}
