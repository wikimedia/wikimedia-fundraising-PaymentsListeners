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
require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		Listener
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_IsAdapterTestCase extends QueueHandlingTestCase
{

	/**
	 * testIsAdapterHasGlobalCollect
	 *
	 * @covers Listener::isAdapter
	 */
	public function testIsAdapterHasGlobalCollect() {

		$adapter = 'GlobalCollect';

		$this->assertTrue( Listener::isAdapter( $adapter ) );
	}

	/**
	 * testIsAdapterDoesNotHaveSomePhonyAdapter
	 *
	 * @covers Listener::isAdapter
	 */
	public function testIsAdapterDoesNotHaveSomePhonyAdapter() {

		$adapter = 'SomePhonyAdapter';

		$this->assertFalse( Listener::isAdapter( $adapter ) );
	}

	/**
	 * testIsAdapterDoesNotHavePayflowpro
	 *
	 * @covers Listener::isAdapter
	 */
	public function testIsAdapterDoesNotHavePayflowpro() {

		$adapter = 'Payflowpro';

		$this->assertFalse( Listener::isAdapter( $adapter ) );
	}
}
