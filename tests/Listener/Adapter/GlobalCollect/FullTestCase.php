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
class Listener_Adapter_GlobalCollect_FullTestCase extends QueueHandlingTestCase
{
	
	/**
	 * testSystemWithPscQueueVerify
	 *
	 * testSystem:
	 * - Form: Use a form that will create a limbo message.
	 * - Psc: Post to the PSC Listener
	 * - Civicrm: Run limbo script
	 * - Queue: Check the limbo queue
	 * - Database: Check limbo database
	 * - Verify: Send the message to the verified queue
	 *
	 * Simulate the form by creating a limbo message and posting to the PSC
	 * Listener. First check the system without CiviCRM Limbo. Nothing should be
	 * in the database with this test. Verify the message and send it to the
	 * verified queue.
	 *
	 * @todo Add confirmation that the message is in the verified queue.
	 *
	 * @todo testSystemWithFormPscQueueDatabaseVerifyWithoutCivicrm
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
	public function testSystemWithPscQueueVerify() {
		
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		// Create a post to the PSC listener.
		$_POST = $this->getPostDataForGlobalCollect();

		// Set the data so we can clear all data
		$adapterInstance->setData( $_POST );
		// Delete from limbo
		$this->removeFromLimboByOrderId();
		// Make sure there is nothing in the database for this test. This is cleanup from other tests.
		$this->removeFromDatabaseByOrderId( $adapterInstance->getData( $adapterInstance->getLimboIdName(), true) );

		// Create a limbo message
		$limboMessageData = $this->getLimboDataForGlobalCollect();

		$queue = $adapterInstance->getQueueLimbo();

		$id = $limboMessageData[ $adapterInstance->getLimboIdName() ];
		
		// Make sure the message was sent to the queue.
		$this->assertTrue( $adapterInstance->pushToQueueWithJmsCorrelationId( $limboMessageData, $queue, $id ) );


		// Create a post to the PSC listener.
		$_POST = $this->getPostDataForGlobalCollect();

		$adapterInstance->setData( $_POST );
		
		
		$this->assertEquals( 'OK', $adapterInstance->receive( $_POST ) );
		
		// Verify message is in the verified queue.
		
	}
	
	/**
	 * testSystemWithPscDatabaseVerify
	 *
	 * Simulate the form by putting a message in the CiviCRM Limbo table.
	 * Verify the message and send it to the verified queue.
	 *
	 * @todo Add confirmation that the message is in the verified queue.
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
	public function testSystemWithPscDatabaseVerify() {
		
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		// Create a limbo message
		$limboMessageData = $this->getLimboDataForGlobalCollect();
		$this->removeFromLimboByOrderId();

		$queue = $adapterInstance->getQueueLimbo();

		$id = $limboMessageData[ $adapterInstance->getLimboIdName() ];
		
		// Make sure the message was sent to the queue.
		$this->assertTrue( $adapterInstance->pushToQueueWithJmsCorrelationId( $limboMessageData, $queue, $id ) );


		// Create a post to the PSC listener.
		$_POST = $this->getPostDataForGlobalCollect();

		$adapterInstance->setData( $_POST );
		
		// Make sure there is nothing in the database for this test. This is cleanup from other tests.
		$this->removeFromDatabaseByOrderId( $adapterInstance->getData( $adapterInstance->getLimboIdName(), true) );
		
		$this->assertEquals( 'OK', $adapterInstance->receive( $_POST ) );
		
		// Verify message is in the verified queue.
		
	}
}
