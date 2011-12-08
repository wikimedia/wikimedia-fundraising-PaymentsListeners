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
 *
 * Db_Adapter_Abstract_ConnectionTestCase
 */
class Db_Adapter_Abstract_ConnectionTestCase extends QueueHandlingTestCase
{

	/**
	 * testConnectionToDefaultDatabaseAdapter
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::setHost
	 * @covers Db_Adapter_Abstract::setDatabase
	 * @covers Db_Adapter_Abstract::setPassword
	 * @covers Db_Adapter_Abstract::setUsername
	 * @covers Db_Adapter_Abstract::setPort
	 * @covers Db_Adapter_Abstract::setSocket
	 * @covers Db_Adapter_Abstract::setFlags
	 * @covers Db_Adapter_Abstract::init
	 * @covers Db_Adapter_Abstract::getHost
	 * @covers Db_Adapter_Abstract::getPassword
	 * @covers Db_Adapter_Abstract::getUsername
	 * @covers Db_Adapter_Abstract::getPort
	 * @covers Db_Adapter_Abstract::getSocket
	 * @covers Db_Adapter_Abstract::getConnection
	 * @covers Db_Adapter_Abstract::getFlags
	 */
	public function testConnectionToDefaultDatabaseAdapter() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
			'port'		=> TESTS_DB_ADAPTER_PORT,
			'socket'	=> '',
			'flags'		=> MYSQLI_CLIENT_INTERACTIVE,
		);

		// The adapter to pass to the factory.
		$adapter = TESTS_DB_ADAPTER_DEFAULT;

		$adapterInstance = Db::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'mysqli', $adapterInstance->getConnection() );
		
		// Check to see if there is a connection to the database.
		$this->assertTrue( $adapterInstance->isConnected() );
	}
}





