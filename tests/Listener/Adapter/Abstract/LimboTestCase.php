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
 * Require QueueHandlingTestCase
 */
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		Listener
 */
class Listener_Adapter_Abstract_LimboTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetLimboIdIsSetToOrderId
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setLimboIdName
	 * @covers Listener_Adapter_Abstract::getLimboIdName
	 */
	public function testGetLimboIdNameIsSetToOrderId() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertSame( 'ORDERID', $adapterInstance->getLimboIdName() );
	}

	/**
	 * testSetLimboIdCanBeSet
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setLimboId
	 * @covers Listener_Adapter_Abstract::getLimboId
	 */
	public function testSetLimboIdCanBeSet() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$value = '1234567890';
		
		$adapterInstance->setLimboId( $value );
		
		$this->assertSame( $value, $adapterInstance->getLimboId() );
	}

	/**
	 * testSetPullFromLimboCanBeSetToTrue
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::getPullFromLimbo
	 * @covers Listener_Adapter_Abstract::setPullFromLimbo
	 */
	public function testSetPullFromLimboCanBeSetToTrue() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );
		
		$adapterInstance->setPullFromLimbo( true );
		
		$this->assertTrue( $adapterInstance->getPullFromLimbo() );
	}

	/**
	 * testSetPullFromLimboCanBeSetToFalse
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::getPullFromLimbo
	 * @covers Listener_Adapter_Abstract::setPullFromLimbo
	 */
	public function testSetPullFromLimboCanBeSetToFalse() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );
		
		$adapterInstance->setPullFromLimbo( false );
		
		$this->assertFalse( $adapterInstance->getPullFromLimbo() );
	}

	/**
	 * testSetPullFromLimboCanBeSet
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::getQueueLimbo
	 * @covers Listener_Adapter_Abstract::setQueueLimbo
	 */
	public function testSetPullFromLimboCanBeSet() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );
		
		$value = '/queue/' . strtolower( __FUNCTION__ );
		
		$adapterInstance->setQueueLimbo( $value );
		
		$this->assertSame( $value, $adapterInstance->getQueueLimbo() );
	}
	
	//getQueueLimbo
	//setQueueLimbo
}
