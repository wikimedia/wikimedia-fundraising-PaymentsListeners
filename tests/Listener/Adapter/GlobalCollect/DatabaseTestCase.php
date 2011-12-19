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
 * @group		ListenerAdapter
 * @group		GlobalCollect
 *
 * Listener_Adapter_GlobalCollect_DatabaseTestCase
 */
class Listener_Adapter_GlobalCollect_DatabaseTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetDb
	 *
	 * @covers Listener_Adapter_Abstract::fetchFromDatabaseByOrderId
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_Abstract::getDb
	 */
	public function testGetDb() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		// Get the Database connection
		$this->assertInstanceOf( 'Db_Adapter_Mysqli', $adapterInstance->getDb() );
	}

	/**
	 * testGetDbWithNoSettingsAndGenerateAnException
	 *
	 * @covers Listener_Adapter_Abstract::fetchFromDatabaseByOrderId
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_Abstract::getDb
	 */
	public function testGetDbWithNoSettingsAndGenerateAnException() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$message = 'Database adapter parameters must be setup in the settings configuration file.';
		$this->setExpectedException( 'Listener_Exception', $message );
		
		// Remove the settings so an exception will be generated.
		$adapterInstance->resetSettings();

		// Get the Database connection
		$adapterInstance->getDb();
	}

	/**
	 * testfetchFromDatabaseByOrderIdWithoutOrderIdAndGenerateAnException
	 *
	 * @covers Listener_Adapter_Abstract::fetchFromDatabaseByOrderId
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_Abstract::getDb
	 */
	public function testfetchFromDatabaseByOrderIdWithoutOrderIdAndGenerateAnException() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		// Clear all data
		$adapterInstance->setData();
		$message = 'The required key is not set in data: ORDERID';
		$this->setExpectedException( 'Listener_Exception', $message );
		
		$order = $adapterInstance->fetchFromDatabaseByOrderId();
		//Debug::dump($order, eval(DUMP) . __FUNCTION__ . PN . _ . "\$order");
		
		//$this->assertTrue( $adapterInstance->pushToQueueWithJmsCorrelationId( $message, $queue, $id ) );
	}

	/**
	 * testfetchFromDatabaseByOrderIdWithEmptyOrderIdAndGenerateAnException
	 *
	 * @covers Listener_Adapter_Abstract::fetchFromDatabaseByOrderId
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_Abstract::getDb
	 */
	public function testfetchFromDatabaseByOrderIdWithEmptyOrderIdAndGenerateAnException() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$_POST = $this->getPostDataForGlobalCollectWithEmptyOrderId();
		
		// Clear all data
		$adapterInstance->setData( $_POST );
		$message = $adapterInstance->getLimboIdName() . ' must not be empty.';
		$this->setExpectedException( 'Listener_Exception', $message );
		
		$order = $adapterInstance->fetchFromDatabaseByOrderId();
		//Debug::dump($order, eval(DUMP) . __FUNCTION__ . PN . _ . "\$order");
		
		//$this->assertTrue( $adapterInstance->pushToQueueWithJmsCorrelationId( $message, $queue, $id ) );
	}

	/**
	 * testfetchFromDatabaseByOrderIdWithAnOrderIdButNotInTheDatabase
	 *
	 * @covers Listener_Adapter_Abstract::fetchFromDatabaseByOrderId
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_Abstract::getDb
	 */
	public function testfetchFromDatabaseByOrderIdWithAnOrderIdButNotInTheDatabase() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$_POST = $this->getPostDataForGlobalCollect();
		
		// Clear all data
		$adapterInstance->setData( $_POST );
		$data = $adapterInstance->getData();
		
		$this->removeFromDatabaseByOrderId( $adapterInstance->getData( $adapterInstance->getLimboIdName(), true) );
		$this->removeFromDatabaseByQueue2CivicrmLimboId( 1 );
		
		//Debug::dump($data, eval(DUMP) . __FUNCTION__ . PN . _ . "\$data");
		//$message = $adapterInstance->getLimboIdName() . ' must not be empty.';
		//$this->setExpectedException( 'Listener_Exception', $message );
		
		$order = $adapterInstance->fetchFromDatabaseByOrderId();
		//Debug::dump($order, eval(DUMP) . __FUNCTION__ . PN . _ . "\$order");
		
		$this->assertFalse( $order );
	}

	/**
	 * testfetchFromDatabaseByOrderIdWithAnOrderIdThatIsInTheDatabase
	 *
	 * @covers Listener_Adapter_Abstract::fetchFromDatabaseByOrderId
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_Abstract::getDb
	 */
	public function testfetchFromDatabaseByOrderIdWithAnOrderIdThatIsInTheDatabase() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$_POST = $this->getPostDataForGlobalCollect();
		
		// Clear all data
		$adapterInstance->setData( $_POST );
		$data = $adapterInstance->getData();
		
		$this->removeFromDatabaseByOrderId( $adapterInstance->getData( $adapterInstance->getLimboIdName(), true) );
		$this->removeFromDatabaseByQueue2CivicrmLimboId( 1 );

		$data = $this->getRowDataForGlobalCollect();
		$table = 'queue2civicrm_limbo';
		$value = $adapterInstance->getDb()->insert( $table, $data );
		
		//Debug::dump($data, eval(DUMP) . __FUNCTION__ . PN . _ . "\$data");
		//$message = $adapterInstance->getLimboIdName() . ' must not be empty.';
		//$this->setExpectedException( 'Listener_Exception', $message );
		
		$this->assertTrue( $adapterInstance->fetchFromDatabaseByOrderId() );
	}
}
