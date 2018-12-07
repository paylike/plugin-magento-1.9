<?php

namespace Magento;


use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Lmc\Steward\Test\AbstractTestCase;

/**
 * @group magento_version_log
 */
class MagentoVersionLogTest extends AbstractTestCase {

	public $runner;

	/**
	 * This is used to store info on a centralized server regarding versions that the test worked on.
	 *
	 * @throws NoSuchElementException
	 * @throws \Facebook\WebDriver\Exception\TimeOutException
	 * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
	 */
	public function testLogVersion() {
		$this->runner = new MagentoRunner( $this );
		$this->runner->ready( array(
				'log_version' => true,
			)
		);
	}
}