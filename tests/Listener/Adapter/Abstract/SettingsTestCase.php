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
 *
 * Listener_Adapter_Abstract_SettingsTestCase
 */
class Listener_Adapter_Abstract_SettingsTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetSettingsHasDbKey
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setSettings
	 * @covers Listener_Adapter_Abstract::getSettings
	 */
	public function testGetSettingsHasDbKey() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertArrayHasKey( 'db', $adapterInstance->getSettings() );
	}

	/**
	 * testGetSettingsWithRequiredKeyDbAndReturnArray
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setSettings
	 * @covers Listener_Adapter_Abstract::getSettings
	 */
	public function testGetSettingsWithRequiredKeyDbAndReturnArray() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$key = 'db';

		$this->assertInternalType( 'array', $adapterInstance->getSettings( $key, true ) );
	}

	/**
	 * testGetSettingsWithInvalidKeyAndReturnNull
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setSettings
	 * @covers Listener_Adapter_Abstract::getSettings
	 */
	public function testGetSettingsWithInvalidKeyAndReturnNull() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$key = 'pickles';

		$this->assertNull( $adapterInstance->getSettings( $key ) );
	}

	/**
	 * testGetSettingsWithRequiredInvalidKeyAndThrowAnException
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setSettings
	 * @covers Listener_Adapter_Abstract::getSettings
	 */
	public function testGetSettingsWithRequiredInvalidKeyAndThrowAnException() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$key = 'pickles';
		
		$message = 'The required key is not set in settings: ' . $key;
		$this->setExpectedException( 'Listener_Exception', $message );

		$adapterInstance->getSettings( $key, true );
	}

	/**
	 * testResetSettings
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::resetSettings
	 * @covers Listener_Adapter_Abstract::setSettings
	 * @covers Listener_Adapter_Abstract::getSettings
	 */
	public function testResetSettings() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$adapterInstance->resetSettings();
		
		$this->assertEmpty( $adapterInstance->getSettings() );
	}

	/**
	 * testSetSettingsWithInvalidFile
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setSettings
	 */
	public function testSetSettingsWithInvalidFile() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = '/tmp/i-am-a-file-that-does-not-exist.ini';

		$message = 'File does not exist for Listener: ' . $file;
		$this->setExpectedException( 'Listener_Exception', $message );
		
		$adapterInstance->setSettings( $file );
	}

	/**
	 * testSetSettingsWithMissingDbSection
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setSettings
	 * @covers Listener_Adapter_Abstract::getPullFromDatabase
	 */
	public function testSetSettingsWithMissingDbSection() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = BASE_PATH . '/tests/resources/settings.database.is.not.set.ini';
		
		$adapterInstance->setSettings( $file );
		$this->assertFalse( $adapterInstance->getPullFromDatabase() );
	}

	/**
	 * testSetSettingsWithDbSectionDisabled
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setSettings
	 * @covers Listener_Adapter_Abstract::getPullFromDatabase
	 */
	public function testSetSettingsWithDbSectionDisabled() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = BASE_PATH . '/tests/resources/settings.database.is.disabled.ini';
		
		$adapterInstance->setSettings( $file );
		$this->assertFalse( $adapterInstance->getPullFromDatabase() );
	}

	/**
	 * testSetSettingsWithDbSectionEnabled
	 *
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setSettings
	 * @covers Listener_Adapter_Abstract::getPullFromDatabase
	 */
	public function testSetSettingsWithDbSectionEnabled() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertTrue( $adapterInstance->getPullFromDatabase() );
	}
}
