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
 * @group		Log
 * @group		LogLevel
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_Abstract_LogLevelTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetLogLevelWhichShouldBeErrByDefault
	 *
	 * @covers Listener_Adapter_Abstract::getLogLevel
	 */
	public function testGetLogLevelWhichShouldBeErrByDefault() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertSame( Listener::LOG_LEVEL_ERR, $adapterInstance->getLogLevel() );
	}

	/**
	 * testSetLogLevelWithInfo
	 *
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::getLogLevel
	 */
	public function testSetLogLevelWithInfo() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// Set to info.
		$adapterInstance->setLogLevel( Listener::LOG_LEVEL_INFO );
		$this->assertSame( Listener::LOG_LEVEL_INFO, $adapterInstance->getLogLevel() );
	}

	/**
	 * testSetLogLevelWithDebug
	 *
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::getLogLevel
	 */
	public function testSetLogLevelWithDebug() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// Set to info.
		$adapterInstance->setLogLevel( Listener::LOG_LEVEL_DEBUG );
		$this->assertSame( Listener::LOG_LEVEL_DEBUG, $adapterInstance->getLogLevel() );
	}

	/**
	 * testSetLogLevelWithInfoThenDebugThenInfoAgain
	 *
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::getLogLevel
	 */
	public function testSetLogLevelWithInfoThenDebugThenInfoAgain() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// Set to info, which is the default. There should be no change in the log level.
		$adapterInstance->setLogLevel( Listener::LOG_LEVEL_INFO );
		$this->assertSame( Listener::LOG_LEVEL_INFO, $adapterInstance->getLogLevel() );

		// Set to debug.
		$adapterInstance->setLogLevel( Listener::LOG_LEVEL_DEBUG );
		$this->assertSame( Listener::LOG_LEVEL_DEBUG, $adapterInstance->getLogLevel() );

		// Set to info again.
		$adapterInstance->setLogLevel( Listener::LOG_LEVEL_INFO );
		$this->assertSame( Listener::LOG_LEVEL_INFO, $adapterInstance->getLogLevel() );
	}

}
