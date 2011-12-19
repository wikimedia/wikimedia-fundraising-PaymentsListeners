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
 * Listener_Adapter_GlobalCollect_LimboTestCase
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

		$this->removeFromLimboByOrderId();
		
		$message = array(
			'MERCHANTID'	=> 9990,
			'ORDERID'		=> 'globalcollect-' . 23,
			'AMOUNT'		=> 100,
			'CURRENCYCODE'	=> 'EUR',
			'STATUS'		=> 525,
		);

		$queue = $adapterInstance->getQueueLimbo();

		$id = 'globalcollect-' . 23;
		
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
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$id = 'globalcollect-' . 23;
		
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
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$id = 'globalcollect-' . 23;
		
		$this->assertTrue( $adapterInstance->fetchFromLimboAndDequeue( $id ) );
	}
	
	/**
	 * testFetchFromLimboWithOutSettingDataWithNoOrderIdAndGenerateAnException
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
	 * @covers Listener_Adapter_Abstract::setData
	 */
	public function testFetchFromLimboWithOutSettingDataWithNoOrderIdAndGenerateAnException() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$message = 'The required key is not set in data: ORDERID';
		$this->setExpectedException( 'Listener_Exception', $message );
		
		$id = '';
		
		$adapterInstance->fetchFromLimbo( $id );
		
	}
	
	/**
	 * testFetchFromLimboWithOutSettingDataAndNoId
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
	 * @covers Listener_Adapter_Abstract::setData
	 */
	public function testFetchFromLimboWithSettingDataAndAnEmptyOrderId() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$message = 'The order_id must be set.';
		$this->setExpectedException( 'Listener_Exception', $message );

		$_POST = $this->getPostDataForGlobalCollectWithEmptyOrderId();

		$adapterInstance->setData( $_POST );
		
		$id = '';
		
		$adapterInstance->fetchFromLimbo( $id );
		
	}
	
	/**
	 * testFetchFromLimboWithOutSettingDataAndNoId
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
	 * @covers Listener_Adapter_Abstract::setData
	 */
	public function testFetchFromLimboWithSettingData() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		//$message = 'The order_id must be set.';
		//$this->setExpectedException( 'Listener_Exception', $message );

		$_POST = $this->getPostDataForGlobalCollect();

		$adapterInstance->setData( $_POST );
		
		$id = '';
		
		$adapterInstance->fetchFromLimbo( $id );
		
	}

}
