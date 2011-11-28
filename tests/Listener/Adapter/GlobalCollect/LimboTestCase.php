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
 * Require
 */
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		ListenerAdapter
 * @group		GlobalCollect
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_GlobalCollect_LimboTestCase extends QueueHandlingTestCase
{

	/**
	 * testPushToQueueWithJmsCorrelationIdIsAbleToPushMessage
	 *
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::openOutputHandle
	 * @covers Listener_Adapter_Abstract::log
	 * @covers Listener_Adapter_Abstract::getAdapterType
	 * @covers Listener_Adapter_Abstract::pushToQueueWithJmsCorrelationId
	 * @covers Listener_Adapter_Abstract::getTxId
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_Abstract::connectStomp
	 * @covers Listener_Adapter_Abstract::stompQueueMessage
	 */
	public function testPushToQueueWithJmsCorrelationIdIsAbleToPushMessage() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$message = array(
			'MERCHANTID'	=> 9990,
			'ORDERID'		=> 'GC-' . 23,
			'AMOUNT'		=> 100,
			'CURRENCYCODE'	=> 'EUR',
			'STATUS'		=> 525,
		);

		$queue = $adapterInstance->getQueueLimbo();

		$id = 'GC-' . 23;
		
		$this->assertTrue( $adapterInstance->pushToQueueWithJmsCorrelationId( $message, $queue, $id ) );
	}
	
	/**
	 * testFetchFromLimbo
	 *
	 * @depends	testPushToQueueWithJmsCorrelationIdIsAbleToPushMessage
	 *
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::openOutputHandle
	 * @covers Listener_Adapter_Abstract::log
	 * @covers Listener_Adapter_Abstract::getAdapterType
	 * @covers Listener_Adapter_Abstract::fetchFromLimbo
	 * @covers Listener_Adapter_Abstract::stompFetchMessage
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_Abstract::connectStomp
	 * @covers Listener_Adapter_Abstract::stompQueueMessage
	 */
	public function testFetchFromLimbo() {

		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_INFO,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$id = 'GC-' . 23;
		
		$this->assertTrue( $adapterInstance->fetchFromLimbo( $id ) );
		
	}
	
	/**
	 * testFetchFromLimboAndDequeue
	 *
	 * @depends	testPushToQueueWithJmsCorrelationIdIsAbleToPushMessage
	 *
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::openOutputHandle
	 * @covers Listener_Adapter_Abstract::log
	 * @covers Listener_Adapter_Abstract::getAdapterType
	 * @covers Listener_Adapter_Abstract::fetchFromLimboAndDequeue
	 * @covers Listener_Adapter_Abstract::fetchFromLimbo
	 * @covers Listener_Adapter_Abstract::stompFetchMessage
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_Abstract::connectStomp
	 * @covers Listener_Adapter_Abstract::stompQueueMessage
	 */
	public function testFetchFromLimboAndDequeue() {

		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_INFO,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$id = 'GC-' . 23;
		
		$this->assertTrue( $adapterInstance->fetchFromLimboAndDequeue( $id ) );
		
	}

}
