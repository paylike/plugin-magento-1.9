<?php

class Paylike_Paymentgateway_Block_Info_Paymentgateway extends Mage_Payment_Block_Info_Cc
{
    protected $_isCheckoutProgressBlockFlag = true;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymentgateway/info/paymentgateway.phtml');
    }
}
