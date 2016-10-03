<?php

class Paylike_Paymentmethod_Block_Info_Paymentmethod extends Mage_Payment_Block_Info_Cc
{
    protected $_isCheckoutProgressBlockFlag = true;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymentmethod/info/paymentmethod.phtml');
    }

}