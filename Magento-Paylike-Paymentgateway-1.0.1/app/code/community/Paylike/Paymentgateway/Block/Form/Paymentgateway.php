<?php

class Paylike_Paymentgateway_Block_Form_Paymentgateway extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        //if( !Mage::getStoreConfig('advanced/modules_disable_output/' . $this->getModuleName()) ) {
            $this->setTemplate('paymentgateway/form/paymentgateway.phtml');
        //}
    }

}
