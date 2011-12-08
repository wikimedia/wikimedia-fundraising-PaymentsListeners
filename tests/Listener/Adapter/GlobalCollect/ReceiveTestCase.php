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
class Listener_Adapter_GlobalCollect_ReceiveTestCase extends QueueHandlingTestCase
{

	/**
	 * testReceiveEmptyShouldReturnNok
	 *
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_Abstract::receiveReturn
	 */
	public function testReceiveEmptyShouldReturnNok() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = array(
		);
		
		$this->assertEquals( 'NOK', $adapterInstance->receive( $_POST ) );
	}
	
	/**
	 * testReceiveValidPostWithPaymentStatusCode1000Paid
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 * @covers Listener_Adapter_Abstract::pushToQueue
	 */
	public function testReceiveValidPostWithPaymentStatusCode1000Paid() {

		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_INFO,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = array(
			'MERCHANTID'		=> "9990",
			'ORDERID'			=> "23",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "8",
			'STATUSID'			=> "800",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$this->assertEquals( 'OK', $adapterInstance->receive( $_POST ) );
		
	}
	
	/**
	 * testReceiveInvalidPostWithEmptyOrderId
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 * @covers Listener_Adapter_Abstract::pushToQueue
	 */
	public function testReceiveInvalidPostWithEmptyOrderId() {

		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_INFO,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = array(
			'MERCHANTID'		=> "9990",
			'ORDERID'			=> "",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "8",
			'STATUSID'			=> "800",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$this->assertEquals( 'NOK', $adapterInstance->receive( $_POST ) );
	}
	
	/**
	 * testReceiveInvalidPostWithoutOrderIdWhileLimboIsEnabled
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 * @covers Listener_Adapter_Abstract::pushToQueue
	 */
	public function testReceiveInvalidPostWithoutOrderIdWhileLimboIsEnabled() {

		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_INFO,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );
		
		$this->setExpectedException( 'Listener_Exception', 'The required key is not set in data: ORDERID');
		
		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = array(
			'MERCHANTID'		=> "9990",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "8",
			'STATUSID'			=> "800",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$this->assertEquals( 'NOK', $adapterInstance->receive( $_POST ) );
	}
	
	/**
	 * testReceiveInvalidPostWithoutOrderIdWhileLimboIsDisabled
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 * @covers Listener_Adapter_Abstract::pushToQueue
	 */
	public function testReceiveInvalidPostWithoutOrderIdWhileLimboIsDisabled() {

		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_INFO,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// Disable pulling from limbo so we can generate a Listener_Exception in Listener_Adapter_GlobalCollect::checkRequiredFields()
		$adapterInstance->setPullFromLimbo( false );
		
		
		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = array(
			'MERCHANTID'		=> "9990",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "8",
			'STATUSID'			=> "800",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$this->assertEquals( 'NOK', $adapterInstance->receive( $_POST ) );
	}
}
