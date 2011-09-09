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
		$directory = dirname( $file );

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
}
