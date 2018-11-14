<?php

namespace Magento;


use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Lmc\Steward\Test\AbstractTestCase;

/**
 * @group magento_quick_test
 */
class MagentoQuickTest extends AbstractTestCase
{

	public $runner;


	/**
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testUsdPaymentBeforeOrderInstant()
	{
		$this->runner = new MagentoRunner($this);
		$this->runner->ready(array(
				'currency' => 'DKK',
				'capture_mode' => 'authorize',
			)
		);
	}
}