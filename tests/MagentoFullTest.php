<?php

namespace Magento;


use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Lmc\Steward\Test\AbstractTestCase;

/**
 * @group magento_full_test
 */
class MagentoFullTest extends AbstractTestCase {

	public $runner;


	/**
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testUsdPaymentInstant() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'capture_mode'           => 'authorize_capture',
			)
		);
	}

	/**
	 *
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testUsdPaymentDelayed() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'               => 'USD',
				'capture_mode'           => 'authorize',
			)
		);
	}

	/**
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testDkkPaymentInstant() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'               => 'DKK',
				'capture_mode'           => 'authorize_capture',
			)
		);
	}

	/**
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testEurPaymentInstant() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'               => 'EUR',
				'capture_mode'           => 'authorize_capture',
			)
		);
	}

	/**
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testJPYPaymentInstant() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'               => 'JPY',
				'capture_mode'           => 'authorize_capture',
			)
		);
	}

	/**
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testRonPaymentInstant() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'               => 'RON',
				'capture_mode'           => 'authorize_capture',
			)
		);
	}

	/**
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testTndPaymentInstant() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'currency'               => 'TND',
				'capture_mode'           => 'authorize_capture',
			)
		);
	}



}
