<?php

class Paylike_Paymentmethod_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Paylike_Paymentmethod_Model_Paymentmethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('paymentmethod')->__('Authorize and Capture')
            ),
            array(
                'value' => Paylike_Paymentmethod_Model_Paymentmethod::ACTION_AUTHORIZE,
                'label' => Mage::helper('paymentmethod')->__('Authorize Only')
            ),
        );
    }

}
