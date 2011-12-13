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
class Listener_ConfigurationFileTestCase extends QueueHandlingTestCase
{

	/**
	 * testDefaultConfigurationFile
	 *
	 * @covers Listener::factory
	 * @covers Listener::getConfigurationFile
	 * @covers Listener::setConfigurationFile
	 */
	public function testDefaultConfigurationFile() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_' . TESTS_LISTENER_ADAPTER_DEFAULT, $adapterInstance );
		$this->assertSame( BASE_PATH . '/settings.ini', Listener::getConfigurationFile() );
	}

	/**
	 * testNonExistentConfigurationFile
	 *
	 * @covers Listener::factory
	 * @covers Listener::getConfigurationFile
	 * @covers Listener::setConfigurationFile
	 */
	public function testNonExistentConfigurationFile() {

		// The parameters to pass to the factory.
		$parameters = array();
		$parameters['settings'] = 'i-do-not-exist.ini';

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$message = 'The configuration file must be a valid path.';
		$this->setExpectedException( 'Listener_Exception', $message );

		$adapterInstance = Listener::factory( $adapter, $parameters );
	}

	/**
	 * testAdapterWithNoSettings
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 */
	public function testAdapterWithNoSettings() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapterClass ='Listener_Adapter_' .  TESTS_LISTENER_ADAPTER_DEFAULT;

		$message = 'Settings are not being loaded. A configuration file must be specified.';
		$this->setExpectedException( 'Listener_Exception', $message );

		$adapterInstance = new $adapterClass( $parameters );
	}

}
