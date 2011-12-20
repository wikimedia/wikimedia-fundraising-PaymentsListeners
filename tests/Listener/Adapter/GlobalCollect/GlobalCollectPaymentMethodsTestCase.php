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
 * Require
 */
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		ListenerAdapter
 * @group		GlobalCollect
 */
class Listener_Adapter_GlobalCollect_GlobalCollectPaymentMethodsTestCase extends QueueHandlingTestCase
{

	/**
	 * testConstructorIsInstanceOfGlobalCollectPaymentMethods
	 *
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::__construct
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::setPaymentMethods
	 */
	public function testConstructorIsInstanceOfGlobalCollectPaymentMethods() {

		$instance = new Listener_Adapter_GlobalCollectPaymentMethods();

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollectPaymentMethods', $instance );
	}

	/**
	 * testHasPaymentMethods
	 *
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::__construct
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::setPaymentMethods
	 */
	public function testHasPaymentMethods() {

		$instance = new Listener_Adapter_GlobalCollectPaymentMethods();

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollectPaymentMethods', $instance );

		$paymentMethods = array(
			1	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'credit',	'label' => 'Credit card online',),
			2	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Credit card batch - offline',),
			3	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Direct Debit',),
			4	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Online Bank Transfer',),
			5	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Check',),
			6	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Invoice',),
			7	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Bank transfer',),
			8	=> array(	'paymentProducts' => array(),	'process'	=> true,	'queue' => 'limbo',		'label' => 'Real-time bank transfer, eWallets',),
			10	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Bank refunds',),
			12	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Payouts',),
			14	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Pre-paid methods',),
			15	=> array(	'paymentProducts' => array(),	'process'	=> true,	'queue' => 'limbo',		'label' => 'Cash',),
		);

		$this->assertSame( $paymentMethods, $instance->getPaymentMethods() );
	}

	/**
	 * testGetPaymentMethodCreditCardOnline
	 *
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::__construct
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::setPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getPaymentMethods
	 */
	public function testGetPaymentMethodCreditCardOnline() {

		$instance = new Listener_Adapter_GlobalCollectPaymentMethods();

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollectPaymentMethods', $instance );

		$paymentMethodId = 1;
		$paymentMethod = $instance->getPaymentMethods( $paymentMethodId );
		
		$this->assertSame( 'Credit card online', $paymentMethod['label'] );
		$this->assertSame( array(), $paymentMethod['paymentProducts'] );
		$this->assertFalse( $paymentMethod['process'] );
		$this->assertSame( 'credit', $paymentMethod['queue'] );
	}

	/**
	 * testGetPaymentMethodRealTimeBankTransfer
	 *
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::__construct
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::setPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getPaymentMethods
	 */
	public function testGetPaymentMethodRealTimeBankTransfer() {

		$instance = new Listener_Adapter_GlobalCollectPaymentMethods();

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollectPaymentMethods', $instance );

		$paymentMethodId = 8;
		$paymentMethod = $instance->getPaymentMethods( $paymentMethodId );
		
		$this->assertSame( 'Real-time bank transfer, eWallets', $paymentMethod['label'] );
		$this->assertSame( array(), $paymentMethod['paymentProducts'] );
		$this->assertTrue( $paymentMethod['process'] );
		$this->assertSame( 'limbo', $paymentMethod['queue'] );
	}

	/**
	 * testGetPaymentMethodRealTimeBankTransfer
	 *
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::__construct
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::setPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getPaymentMethods
	 */
	public function testGetPaymentMethodThatDoesNotExist() {

		$instance = new Listener_Adapter_GlobalCollectPaymentMethods();

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollectPaymentMethods', $instance );

		$paymentMethodId = -1;
		$message = 'The payment method id was not found: ' . $paymentMethodId;
		$this->setExpectedException( 'Listener_Exception', $message );

		$instance->getPaymentMethods( $paymentMethodId );
	}

	/**
	 * testGetProcessDecisionForRealTimeBankTransferWhichShouldBeTrue
	 *
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::__construct
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::setPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getProcessDecision
	 */
	public function testGetProcessDecisionForRealTimeBankTransferWhichShouldBeTrue() {

		$instance = new Listener_Adapter_GlobalCollectPaymentMethods();

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollectPaymentMethods', $instance );

		$paymentMethodId = 8;

		$this->assertTrue( $instance->getProcessDecision( $paymentMethodId ) );
	}

	/**
	 * testGetProcessDecisionForCashWhichShouldBeTrue
	 *
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::__construct
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::setPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getProcessDecision
	 */
	public function testGetProcessDecisionForCashWhichShouldBeTrue() {

		$instance = new Listener_Adapter_GlobalCollectPaymentMethods();

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollectPaymentMethods', $instance );

		$paymentMethodId = 15;

		$this->assertTrue( $instance->getProcessDecision( $paymentMethodId ) );
	}

	/**
	 * testGetProcessDecisionForPayoutsWhichShouldBeFalse
	 *
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::__construct
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::setPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getPaymentMethods
	 * @covers Listener_Adapter_GlobalCollectPaymentMethods::getProcessDecision
	 */
	public function testGetProcessDecisionForPayoutsWhichShouldBeFalse() {

		$instance = new Listener_Adapter_GlobalCollectPaymentMethods();

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollectPaymentMethods', $instance );

		$paymentMethodId = 12;

		$this->assertFalse( $instance->getProcessDecision( $paymentMethodId ) );
	}
}
