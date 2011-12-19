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
	 * @covers Listener_Adapter_GlobalCollect::getProcessDecision
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
	 * testReceiveValidPost
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::getProcessDecision
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 */
	public function testReceiveValidPost() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollect();
		
		$this->assertEquals( 'OK', $adapterInstance->receive( $_POST ) );
		
	}
	
	/**
	 * testReceiveInvalidPostWithEmptyOrderIdWithOutPullingFromDatabase
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::getProcessDecision
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 */
	public function testReceiveInvalidPostWithEmptyOrderIdWithOutPullingFromDatabase() {

		// The parameters to pass to the factory.
		$parameters = array();

		// Database is disabled
		$parameters['settings'] = BASE_PATH . '/tests/resources/settings.database.is.disabled.ini';
		
		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollectWithEmptyOrderId();

		$this->setExpectedException( 'Listener_Exception', 'The order_id must be set.');
		
		$adapterInstance->receive( $_POST );
	}
	
	/**
	 * testReceiveInvalidPostWithoutOrderIdWhileLimboIsEnabled
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::getProcessDecision
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 */
	public function testReceiveInvalidPostWithoutOrderIdWhileLimboIsEnabled() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );
		
		$this->setExpectedException( 'Listener_Exception', 'The required key is not set in data: ORDERID');
		
		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollectWithOutOrderId();
		
		$this->assertEquals( 'NOK', $adapterInstance->receive( $_POST ) );
	}
	
	/**
	 * testReceiveInvalidPostWithoutOrderIdWhileLimboIsDisabled
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::getProcessDecision
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 */
	public function testReceiveInvalidPostWithoutOrderIdWhileLimboIsDisabled() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// Disable pulling from limbo so we can generate a Listener_Exception in Listener_Adapter_GlobalCollect::checkRequiredFields()
		$adapterInstance->setPullFromLimbo( false );
		
		
		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$message = 'The required key is not set in data: ORDERID';
		$this->setExpectedException( 'Listener_Exception', $message );

		$_POST = $this->getPostDataForGlobalCollectWithOutOrderId();
		
		$adapterInstance->receive( $_POST );
	}
	
	/**
	 * testReceiveValidPostWithAPaymentMethodWeDoNotProcessCash
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::getProcessDecision
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 */
	public function testReceiveValidPostWithAPaymentMethodWeDoNotProcessCash() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$data = array();
		$data['PAYMENTMETHODID'] = 15;
		$_POST = $this->getPostDataForGlobalCollect( $data );
		
		$this->assertEquals( 'OK', $adapterInstance->receive( $_POST ) );
		
	}
	
	/**
	 * testReceiveValidPostThatDoesNotExistInLimboOrDatabase
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::receive
	 * @covers Listener_Adapter_GlobalCollect::getProcessDecision
	 * @covers Listener_Adapter_GlobalCollect::receiveReturn
	 * @covers Listener_Adapter_GlobalCollect::parse
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::messageSanityCheck
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 * @covers Listener_Adapter_GlobalCollect::verifyPaymentNotification
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::pushToVerified
	 * @covers Listener_Adapter_Abstract::stompDequeueMessage
	 * @covers Listener_Adapter_Abstract::getInDatabase
	 * @covers Listener_Adapter_Abstract::setInDatabase
	 * @covers Listener_Adapter_Abstract::getInLimbo
	 * @covers Listener_Adapter_Abstract::setInLimbo
	 */
	public function testReceiveValidPostThatDoesNotExistInLimboOrDatabase() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollect();

		$adapterInstance->setData( $_POST );
		
		$this->removeFromDatabaseByOrderId( $adapterInstance->getData( $adapterInstance->getLimboIdName(), true) );

		$this->removeFromLimboByOrderId();
		
		$this->assertEquals( 'OK', $adapterInstance->receive( $_POST ) );
		
	}
}
