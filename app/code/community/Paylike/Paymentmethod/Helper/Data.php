<?php
class Paylike_Paymentmethod_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    public function getPopupTitle()
    {
        return Mage::getStoreConfig('payment/paymentmethod/pop_up_title');
    }

    public function getPopupDescription()
    {
        return Mage::getStoreConfig('payment/paymentmethod/pop_up_description');
    }

    public function getPublicKey()
    {
        return Mage::getStoreConfig('payment/paymentmethod/public_key');
    }

}