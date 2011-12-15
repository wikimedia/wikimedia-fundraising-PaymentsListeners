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
 * Db_Adapter_Mysqli_UpdateTestCase
 */
class Db_Adapter_Mysqli_UpdateTestCase extends QueueHandlingTestCase
{

	/**
	 * testDeleteThenInsertThenUpdateAndFetchTheRecord
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::delete
	 * @covers Db_Adapter_Abstract::insert
	 * @covers Db_Adapter_Abstract::query
	 * @covers Db_Adapter_Abstract::update
	 * @covers Db_Adapter_Abstract::getLastInsertId
	 * @covers Db_Adapter_Abstract::quoteInto
	 * @covers Db_Adapter_Mysqli::setLastInsertId
	 * @covers Db_Adapter_Mysqli::getErrorCode
	 * @covers Db_Adapter_Mysqli::lastInsertId
	 * @covers Db_Adapter_Mysqli::fetch
	 * @covers Db_Expression::__construct
	 * @covers Db_Expression::__toString
	 *
	 */
	public function testDeleteThenInsertThenUpdateAndFetchTheRecord() {

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

		$query = "SELECT * FROM `queue2civicrm_limbo` where `order_id` = '?'";
		$adapterInstance->query( $adapterInstance->quoteInto( $query, $orderId ) );

		$row = $adapterInstance->fetch();
		
		$this->assertArrayHasKey( 'queue2civicrm_limbo_id', $row );
		
		// $id will be a string from the database.
		$this->assertSame( (string) $id, $row['queue2civicrm_limbo_id'] );

		$oldData = 'Just some data that should be in json format.';
		
		$data = array(
			'queue2civicrm_limbo_id'		=> $id,
			'contribution_tracking_id'	=> new Db_Expression(1),
			'order_id'					=> $orderId,
			'timestamp'					=> time(),
			'data'						=> $oldData,
			'gateway'					=> strtolower( TESTS_LISTENER_ADAPTER_DEFAULT ),
			'payment_method'			=> 'rtbt',
			'payment_submethod'			=> 'rtbt_ideal',
		);
		
		$query = "`queue2civicrm_limbo`.`queue2civicrm_limbo_id` = '?'";
		$where = $adapterInstance->quoteInto( $query, $id );
		
		$newData = 'Just some data that was changed.';
		
		$data['data'] = $newData;
		//$options = array( 'stopperKill' => eval( DUMP ) ) ;
		$value = $adapterInstance->update( $table, $data, $where );
		
		return;
		// Get the row and check to see if it was updated.
		$query = "SELECT * FROM `queue2civicrm_limbo` where `order_id` = '?'";
		$adapterInstance->query( $adapterInstance->quoteInto( $query, $orderId ) );

		$row = $adapterInstance->fetch();
		
		$this->assertArrayHasKey( 'queue2civicrm_limbo_id', $row );
		
		// $id will be a string from the database.
		$this->assertSame( $newData, $row['data'] );

	}

	/**
	 * testUpdateWithoutSpecifyingATableAndGenerateAnException
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::update
	 *
	 */
	public function testUpdateWithoutSpecifyingATableAndGenerateAnException() {

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
		$where = array();

		$message = '$table cannot be empty.';
		$this->setExpectedException( 'Db_Exception', $message );
		
		// Attempt to update
		$adapterInstance->update( $table, $data, $where );
	}

	/**
	 * testUpdateWithoutSpecifyingAnyDataAndGenerateAnException
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::update
	 *
	 */
	public function testUpdateWithoutSpecifyingAnyDataAndGenerateAnException() {

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

		$table = 'queue2civicrm_limbo';
		$data = array();
		$where = array();

		$message = '$data cannot be empty.';
		$this->setExpectedException( 'Db_Exception', $message );
		
		// Attempt to update
		$adapterInstance->update( $table, $data, $where );
	}

	/**
	 * testUpdateWithAnEmptyWhereClauseAndGenerateAnException
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::update
	 *
	 */
	public function testUpdateWithAnEmptyWhereClauseAndGenerateAnException() {

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

		$table = 'queue2civicrm_limbo';
		$data = array( 'some' => 'thing', );
		$where = array();

		$message = '$where cannot be empty.';
		$this->setExpectedException( 'Db_Exception', $message );
		
		// Attempt to update
		$adapterInstance->update( $table, $data, $where );
	}
}
