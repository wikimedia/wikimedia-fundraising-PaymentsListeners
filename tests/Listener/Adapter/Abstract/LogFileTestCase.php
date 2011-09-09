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
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_Abstract_LogFileTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetLogFileWhichShouldBeEmpty
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 */
	public function testGetLogFileWhichShouldBeEmpty() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertEmpty( $adapterInstance->getLogFile() );
	}

	/**
	 * testGetLogFileWhichShouldBeEmpty
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 * @covers Listener_Adapter_Abstract::setLogFile
	 */
	public function testSetLogFileWithDefaultLogFile() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		$adapterInstance->setLogFile();

		$this->assertSame( $file, $adapterInstance->getLogFile() );
	}
}
