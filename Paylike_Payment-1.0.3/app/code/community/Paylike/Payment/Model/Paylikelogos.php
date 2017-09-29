<?php

class Paylike_Payment_Model_Paylikelogos extends Mage_Core_Model_Abstract {

    const PAYMENT_LOGO_PATH = '/frontend/paylike/logos/';

    protected function _construct() {
        $this->_init('paylike_payment/paylikelogos');
    }

}
