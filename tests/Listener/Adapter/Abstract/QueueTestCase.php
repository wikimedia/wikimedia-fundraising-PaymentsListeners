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
 * @group		Listener
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_Abstract_QueueTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetQueuePendingIsNotEmptyByDefault
	 *
	 * @covers Listener_Adapter_Abstract::getQueuePending
	 */
	public function testGetQueuePendingIsNotEmptyByDefault() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertNotEmpty( $adapterInstance->getQueuePending() );
	}

	/**
	 * testSetQueuePending
	 *
	 * @covers Listener_Adapter_Abstract::setQueuePending
	 * @covers Listener_Adapter_Abstract::getQueuePending
	 */
	public function testSetQueuePending() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$queuePending = 'duck';
		$adapterInstance->setQueuePending( $queuePending );
		
		$this->assertSame( $queuePending, $adapterInstance->getQueuePending() );
	}

	/**
	 * testGetQueueVerifiedIsNotEmptyByDefault
	 *
	 * @covers Listener_Adapter_Abstract::getQueueVerified
	 */
	public function testGetQueueVerifiedIsNotEmptyByDefault() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertNotEmpty( $adapterInstance->getQueueVerified() );
	}

	/**
	 * testSetQueueVerified
	 *
	 * @covers Listener_Adapter_Abstract::setQueueVerified
	 * @covers Listener_Adapter_Abstract::getQueueVerified
	 */
	public function testSetQueueVerified() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$queueVerified = 'duck';
		$adapterInstance->setQueueVerified( $queueVerified );
		
		$this->assertSame( $queueVerified, $adapterInstance->getQueueVerified() );
	}
}
