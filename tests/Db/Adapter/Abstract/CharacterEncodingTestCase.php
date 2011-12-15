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
 * Db_Adapter_Abstract_CharacterEncodingTestCase
 */
class Db_Adapter_Abstract_CharacterEncodingTestCase extends QueueHandlingTestCase
{

	/**
	 * testSetCharacterEncoding
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::setCharacterEncoding
	 * @covers Db_Adapter_Abstract::getCharacterEncoding
	 */
	public function testSetCharacterEncoding() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
		);

		// The adapter to pass to the factory.
		$adapter = TESTS_DB_ADAPTER_DEFAULT;

		$adapterInstance = Db::factory( $adapter, $parameters );

		$defaultCharacterEncoding = 'utf8';

		$this->assertSame( $defaultCharacterEncoding, $adapterInstance->getCharacterEncoding() );

		$characterEncoding = 'latin1';
		$adapterInstance->setCharacterEncoding( $characterEncoding );

		$this->assertSame( $characterEncoding, $adapterInstance->getCharacterEncoding() );
		
		//$this->assertInstanceOf( 'mysqli', $adapterInstance->getConnection() );
		
		// Check to see if there is a connection to the database.
		//$this->assertTrue( $adapterInstance->isConnected() );
	}

	/**
	 * testCharacterEncodingOnDatabaseConnectionAndVerifyItIsUtf8
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::setCharacterEncoding
	 * @covers Db_Adapter_Abstract::getCharacterEncoding
	 * @covers Db_Adapter_Mysqli::query
	 * @covers Db_Adapter_Mysqli::quoteInto
	 * @covers Db_Adapter_Mysqli::fetch
	 */
	public function testCharacterEncodingOnDatabaseConnectionAndVerifyItIsUtf8() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
		);

		// The adapter to pass to the factory.
		$adapter = TESTS_DB_ADAPTER_DEFAULT;

		$adapterInstance = Db::factory( $adapter, $parameters );

		$defaultCharacterEncoding = 'utf8';

		$this->assertSame( $defaultCharacterEncoding, $adapterInstance->getCharacterEncoding() );

		$query = "SHOW VARIABLES LIKE '?'";
		$adapterInstance->query( $adapterInstance->quoteInto( $query, 'character_set_connection' ) );
		//$adapterInstance->query( $adapterInstance->quoteInto( $query, 'character%' ) );

		$row = $adapterInstance->fetch();
		
		$this->assertArrayHasKey( 'Variable_name', $row );
		$this->assertArrayHasKey( 'Value', $row );
		$this->assertSame( $defaultCharacterEncoding, $row['Value'] );
		//$row = $adapterInstance->fetchAll( array( 'key' => 'Variable_name' ) );
		//Debug::dump($row, eval(DUMP) . __FUNCTION__ . PN . _ . "\$row");

		//$characterEncoding = 'latin1_swedish_ci';
		//$adapterInstance->setCharacterEncoding( $characterEncoding );

		//$this->assertSame( $characterEncoding, $adapterInstance->getCharacterEncoding() );
		
		//$this->assertInstanceOf( 'mysqli', $adapterInstance->getConnection() );
		
		// Check to see if there is a connection to the database.
		//$this->assertTrue( $adapterInstance->isConnected() );
	}

	/**
	 * testChangingCharacterEncodingOnDatabaseConnectionAndVerifyItIsLatin1
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Abstract::setCharacterEncoding
	 * @covers Db_Adapter_Abstract::getCharacterEncoding
	 * @covers Db_Adapter_Mysqli::query
	 * @covers Db_Adapter_Mysqli::quoteInto
	 * @covers Db_Adapter_Mysqli::fetch
	 */
	public function testChangingCharacterEncodingOnDatabaseConnectionAndVerifyItIsLatin1() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
		);

		// The adapter to pass to the factory.
		$adapter = TESTS_DB_ADAPTER_DEFAULT;

		$adapterInstance = Db::factory( $adapter, $parameters );

		$defaultCharacterEncoding = 'utf8';

		$this->assertSame( $defaultCharacterEncoding, $adapterInstance->getCharacterEncoding() );

		$characterEncoding = 'latin1';
		$adapterInstance->setCharacterEncoding( $characterEncoding );

		$query = "SHOW VARIABLES LIKE '?'";
		$adapterInstance->query( $adapterInstance->quoteInto( $query, 'character%' ) );

		$row = $adapterInstance->fetchAll( array( 'key' => 'Variable_name' ) );
		//Debug::puke($row, eval(DUMP) . __FUNCTION__ . PN . _ . "\$row");
		
		$this->assertArrayHasKey( 'character_set_client', $row );
		$this->assertArrayHasKey( 'character_set_connection', $row );
		$this->assertArrayHasKey( 'character_set_results', $row );
		$this->assertSame( $characterEncoding, $row['character_set_results']['Value'] );
		$this->assertSame( $characterEncoding, $row['character_set_connection']['Value'] );
		$this->assertSame( $characterEncoding, $row['character_set_results']['Value'] );
	}
}





