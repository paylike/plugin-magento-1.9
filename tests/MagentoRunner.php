<?php


namespace Magento;

use Facebook\WebDriver\Exception\NoAlertOpenException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MagentoRunner extends MagentoTestHelper
{

    /**
     * @param $args
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    public function ready($args) {
        $this->set($args);
        $this->go();
    }

    /**
     * @throws TimeOutException
     */
    public function loginAdmin() {
        try {
            $this->goToPage('admin', '#username');
            while ( ! $this->hasValue('#username', $this->user)) {
                $this->typeLogin();
            }
            $this->click('.form-buttons input');
            $this->waitForElement('#store_switcher');
        } catch (NoSuchElementException $exception) {
            // we may already be logged in
        }
        try {
            $this->click('.message-popup-head a');
        } catch (NoSuchElementException $exception) {
            // its optional
        }
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    public function disableEmail() {
        if ($this->stop_email === true) {
            $this->goToPage('admin/system_config/edit/section/system', '#system_smtp-head');

            $this->click('#system_smtp-head');
            try {
                $this->waitElementAppear('#system_smtp_disable');
            } catch (TimeOutException $exception) {
                // click again if it doesn't show up
                $this->click('#system_smtp-head');
            }
            $this->selectValue('#system_smtp_disable', 1);
            $this->submitAdmin();
            $this->waitForElement('.messages .success-msg');
        }
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    public function changeMode() {
        $this->goToPage('admin/system_config/edit/section/payment/', '#payment_paylike-head');
        $this->captureMode();
        $this->submitAdmin();
    }

    /**
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    public function captureMode() {
        $this->selectValue('#payment_paylike_payment_action', $this->capture_mode);

    }

    /**
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function logInFrontend() {
        $this->goToPage('customer/account/login/', '#email');
        while ( ! $this->hasValue('#email', $this->email)) {
            $this->type('#email', $this->email);
            $this->type('#pass', $this->pass);
        }
        $this->click('.registered-users .buttons-set .button');
        $this->waitForPage('customer/account/');
    }

    /**
     *
     */
    public function changeCurrency() {
        $this->click("//*[@id='select-currency']/option[text()[contains(.,'" . $this->currency . "')]]");
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function clearCartItem() {

        $this->click('.header-minicart');
        $productRemoves = $this->findElements('#cart-sidebar li a.remove');
        if (count($productRemoves) > 1) {
            try {
                $productRemoves[0]->click();
                $this->wd->switchTo()->alert()->accept();
            } catch (StaleElementReferenceException $exception) {
                // can happen
            }
            $this->waitElementDisappear('.block-content');
            $this->clearCartItem();
        }

    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function addToCart() {
        $this->goToPage('');
        $this->click('.products-grid.products-grid--max-5-col-widget li a[title="Tori Tank"]');
        $this->waitForElement('.product-view');
        $this->click(".product-view ul#configurable_swatch_color li:first-child");
        $this->click(".product-view ul#configurable_swatch_size li:first-child");
        $this->click('.add-to-cart-buttons button.btn-cart');
        $this->waitForElement('.messages .success-msg');
    }

    /**
     *
     */
    public function submitAdmin() {
        $this->click('.content-header td.form-buttons button.save');
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function proceedToCheckout() {
        $this->goToPage('checkout/onepage/', '.checkout-onepage-index');
    }

    /**
     *
     */
    public function processAddress() {
        $this->click('.opc-firststep-billing button[title="Continue"]');
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function processShipping() {
        $this->waitForElement(WebDriverBy::cssSelector('#shipping-method-buttons-container .button'));
        try {
            $this->click(WebDriverBy::cssSelector('#co-shipping-method-form ul li:first-child #s_method_freeshipping_freeshipping'));
        } catch (NoSuchElementException $exception) {
            // already selected
        }
        $this->click(WebDriverBy::cssSelector('#shipping-method-buttons-container .button'));
        $this->waitForElement(WebDriverBy::cssSelector('#payment-buttons-container .button'));
    }


    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws TimeOutException
     */
    public function choosePaylike() {

        $this->click('#p_method_paylike');
        $this->click($this->main_test->findByCss('#payment-buttons-container .button'));
        $this->waitForElement('#checkout-review-load');
        $this->click($this->main_test->findByCss('#review-buttons-container .button.btn-checkout'));

    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function finalPaylike() {

        $amount         = (int)$this->wd->executeScript("return window.paylikeminoramount");
        $expectedAmount = $this->getText('.a-right.last strong .price');
        $expectedAmount = preg_replace("/[^0-9.]/", "", $expectedAmount);
        $expectedAmount = trim($expectedAmount, '.');
        $expectedAmount = ceil(round($expectedAmount, 3) * get_paylike_currency_multiplier($this->currency));
        $this->main_test->assertEquals($expectedAmount, $amount, "Checking minor amount for " . $this->currency);
        $this->popupPaylike();
        //wait for payment redirect
        $this->wd->wait(60, 3000)->until(
            WebDriverExpectedCondition::titleIs('Magento Commerce')
        );

        $this->main_test->assertEquals('THANK YOU FOR YOUR PURCHASE!', $this->getText('.sub-title'),
            "Checking success message for " . $this->currency);
    }

    /**
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function popupPaylike() {
        $this->waitForElement('.paylike.overlay .payment form #card-number');
        $this->type('.paylike.overlay .payment form #card-number', 41000000000000);
        $this->type('.paylike.overlay .payment form #card-expiry', '11/22');
        $this->type('.paylike.overlay .payment form #card-code', '122');
        $this->click('.paylike.overlay .payment form button');
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function confirmOrder() {
        $this->click($this->main_test->findByCss('#checkout-review-submit button'));
    }


    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function selectOrder() {
        $this->loginAdmin();
        $this->goToPage('admin/sales_order/', '#sales_order_grid_table');
        $this->click($this->main_test->findByCss('#sales_order_grid_table tbody tr:first-child a'));
        $this->waitForElement('#sales_order_view');
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function capture() {

        $this->click("//*[@class='content-header']/p/button/span/span/span[contains(text(), 'Invoice')]");
        $this->waitForElement('.adminhtml-sales-order-invoice-new');
        $this->click('.order-totals-bottom button.submit-button');
        $this->waitForElement('.messages .success-msg');
        $this->main_test->assertEquals('The invoice has been created.', $this->getText('.messages .success-msg'),
            "Checking capture message for " . $this->currency);

    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function refund() {
        $this->click('#sales_order_view_tabs_order_invoices');
        $this->waitElementAppear('#sales_order_view_tabs_order_invoices_content');
        $this->click($this->main_test->findByCss('#order_invoices_table tbody tr:first-child'));
        $this->waitForElement('.adminhtml-sales-order-invoice-view');
        $this->click("//*[@class='content-header']/p/button/span/span/span[text()= 'Credit Memo']");
        $this->waitForElement('.adminhtml-sales-order-creditmemo-new');
        $this->click("//*[@class='order-totals-bottom']/button/span/span/span[text()= 'Refund']");
        $this->waitForElement('.messages .success-msg');
        $this->main_test->assertEquals('The credit memo has been created.', $this->getText('.messages .success-msg'),
            "Checking refund message for " . $this->currency);
    }


    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    private function directPayment() {
        $this->logInFrontend();
        $this->changeCurrency();
        $this->clearCartItem();
        $this->addToCart();
        $this->proceedToCheckout();
        $this->processAddress();
        $this->processShipping();
        $this->choosePaylike();
        $this->finalPaylike();
        $this->selectOrder();
        if ($this->capture_mode == 'authorize') { //delayed
            $this->capture();
        }
        $this->refund();
    }

    /**
     *  Insert user and password on the login screen
     */
    private function typeLogin() {
        $this->type('#username', $this->user);
        $this->type('#login', $this->pass);
    }

    /**
     *  Insert user and password on the login screen
     */
    private function typeConnectLogin() {
        $this->type('#username', $this->user);
        $this->type('#password', $this->pass);
    }


    /**
     * @param $args
     */
    private function set($args) {
        foreach ($args as $key => $val) {
            $name = $key;
            if (isset($this->{$name})) {
                $this->{$name} = $val;
            }
        }
    }


    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    private function settings() {
        $this->disableEmail();
        $this->changeMode();
    }

    /**
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws NoSuchElementException
     */
    private function getVersions() {
        try {
            $this->goToPage('downloader/', '#username');
            while ( ! $this->hasValue('#username', $this->user)) {
                $this->typeConnectLogin();
            }
            $this->click('.content form button');
            $this->waitForElement('.content form.connect-packages');
        } catch (NoSuchElementException $exception) {
            // we may already be logged in
        }
        $woo     = $this->getPluginVersion('Mage_All_Latest');
        $paylike = $this->getPluginVersion('Paylike_Payment');

        return ['ecommerce' => $woo, 'plugin' => $paylike];
    }

    /**
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    private function outputVersions() {
        $versions = $this->getVersions();
        $this->main_test->log('----VERSIONS----');
        $this->main_test->log('WooCommerce %s', $versions['ecommerce']);
        $this->main_test->log('Paylike %s', $versions['plugin']);
    }

    /**
     * @param $text
     *
     * @return string
     * @throws NoSuchElementException
     */
    private function getPluginVersion($text) {
        $element = $this->findChild('.a-center',
            $this->main_test->findByXpath("//*//table/tbody/tr/td[text()='" . $text . "']/.."));
        $version = $this->getText($element);
        $version = trim($version, '&nbsp;');

        return $version;
    }

    /**
     * @throws NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    private function logVersionsRemotly() {
        $versions = $this->getVersions();
        //$this->outputVersions();
        $this->wd->get(getenv('REMOTE_LOG_URL') . '&key=' . $this->get_slug($versions['ecommerce']) . '&tag=magento19&view=html&' . http_build_query($versions));
        $this->waitForElement('#message');
        $message = $this->getText('#message');
        $this->main_test->assertEquals('Success!', $message, "Remote log failed");
    }


    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    private function go() {
        $this->changeWindow();
        if ($this->log_version) {
            $this->logVersionsRemotly();

            return $this;
        }
        $this->loginAdmin();
        $this->settings();
        $this->directPayment();

    }

    /**
     *
     */
    private function changeWindow() {
        $this->wd->manage()->window()->setSize(new WebDriverDimension(1280, 1080));
    }


}

