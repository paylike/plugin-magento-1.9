<?php

class Paylike_Paymentgateway_Model_Source_PaymentMode
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'test',
                'label' => Mage::helper('paymentgateway')->__('Test')
            ),
            array(
                'value' => 'live',
                'label' => Mage::helper('paymentgateway')->__('Live')
            ),
        );
    }
}

