<?php

class Paylike_Paymentgateway_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Paylike_Paymentgateway_Model_Paymentgateway::ACTION_AUTHORIZE,
                'label' => Mage::helper('paymentgateway')->__('Delayed') //Authorize Only
            ),
            array(
                'value' => Paylike_Paymentgateway_Model_Paymentgateway::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('paymentgateway')->__('Instant') //Authorize and Capture
            ),
        );
    }
}

