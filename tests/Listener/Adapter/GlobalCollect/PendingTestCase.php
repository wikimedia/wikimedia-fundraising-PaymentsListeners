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
class Listener_Adapter_GlobalCollect_PendingTestCase extends QueueHandlingTestCase
{

	/**
	 * testFetchFromPendingShouldFailWhenThereAreNoMessagesInTheQueue
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 */
	public function testFetchFromPendingShouldFailWhenThereAreNoMessagesInTheQueue() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );
		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollect();
		// Set the default data to clear the queue.
		$adapterInstance->setData( $_POST );
		// Delete from limbo
		$this->removeFromLimboByOrderId();
		// Make sure there is nothing in the database for this test. This is cleanup from other tests.
		$this->removeFromDatabaseByOrderId( $adapterInstance->getData( $adapterInstance->getLimboIdName(), true) );

		$this->assertFalse( $adapterInstance->fetchFromPending() );
	}

	/**
	 * testPushToPending
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::pushToPending
	 * @covers Listener_Adapter_Abstract::fetchFromPending
	 * @covers Listener_Adapter_Abstract::fetchFromPendingAndDequeue
	 */
	public function testPushToPending() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );
		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollect();
		// Set the default data to clear the queue.
		$adapterInstance->setData( $_POST );

		$adapterInstance->pushToPending( $_POST );
		$adapterInstance->fetchFromPendingAndDequeue();
	}
}
