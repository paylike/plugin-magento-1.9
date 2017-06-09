<?php

class Paylike_Paymentgateway_Model_Source_PaymentModuleStatus
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 1,
                'label' => Mage::helper('paymentgateway')->__('Enabled')
            ),
            array(
                'value' => 0,
                'label' => Mage::helper('paymentgateway')->__('Disabled')
            ),
        );
    }
}

