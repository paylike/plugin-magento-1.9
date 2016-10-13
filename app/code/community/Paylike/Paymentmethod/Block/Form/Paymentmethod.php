<?php

class Paylike_Paymentmethod_Block_Form_Paymentmethod extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymentmethod/form/paymentmethod.phtml');
    }

}
