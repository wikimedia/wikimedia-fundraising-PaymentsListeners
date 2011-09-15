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
class Listener_Adapter_Abstract_ConstructorTestCase extends QueueHandlingTestCase
{

	/**
	 * testConstructorIsInstanceOfGlobalCollect
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 */
	public function testConstructorIsInstanceOfGlobalCollect() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
	}

	/**
	 * testConstructorParametersWithSetStompPathToATestStompFile
	 *
	 * @covers Listener_Adapter_Abstract::getActiveMqStompUri
	 * @covers Listener_Adapter_Abstract::setActiveMqStompUri
	 * @covers Listener_Adapter_Abstract::__construct
	 */
	public function testConstructorParametersWithSetActiveMqStompUriWithLocalhostAndPort61614() {

		$activeMqStompUri = 'tcp://localhost:61614';

		// The parameters to pass to the factory.
		$parameters = array(
			'activeMqStompUri' => $activeMqStompUri,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertSame( $activeMqStompUri, $adapterInstance->getActiveMqStompUri() );
	}

	/**
	 * testConstructorParametersWithSetLogLevelToInfo
	 *
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::getLogLevel
	 * @covers Listener_Adapter_Abstract::__construct
	 */
	public function testConstructorParametersWithSetLogLevelToInfo() {

		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_INFO,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertSame( Listener::LOG_LEVEL_INFO, $adapterInstance->getLogLevel() );
	}

	/**
	 * testConstructorParametersWithSetLogFileToATestDirectory
	 *
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::getLogLevel
	 * @covers Listener_Adapter_Abstract::__construct
	 */
	public function testConstructorParametersWithSetLogFileToATestDirectory() {

		$file = BASE_PATH . '/tests/resources/log-directory-for-adapter-testing/out.log';

		// The parameters to pass to the factory.
		$parameters = array(
			'logFile' => $file,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$adapterInstance->setLogFile( $file );

		$this->assertSame( $file, $adapterInstance->getLogFile() );
	}

	/**
	 * testConstructorParametersWithSetStompPathToATestStompFile
	 *
	 * @covers Listener_Adapter_Abstract::getStompPath
	 * @covers Listener_Adapter_Abstract::setStompPath
	 * @covers Listener_Adapter_Abstract::__construct
	 */
	public function testConstructorParametersWithSetStompPathToATestStompFile() {

		$path = BASE_PATH . '/tests/resources/activeMQ/Stomp.php';

		// The parameters to pass to the factory.
		$parameters = array(
			'stompPath' => $path,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertSame( $path, $adapterInstance->getStompPath() );
	}
}
