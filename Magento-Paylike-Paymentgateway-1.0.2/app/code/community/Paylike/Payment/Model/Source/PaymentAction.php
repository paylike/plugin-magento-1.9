<?php

class Paylike_Payment_Model_Source_PaymentAction {

    public function toOptionArray() {
        return array(
            array(
                'value' => Paylike_Payment_Model_Paylike::ACTION_AUTHORIZE,
                'label' => Mage::helper('paylike_payment')->__('Delayed') //Authorize Only
            ),
            array(
                'value' => Paylike_Payment_Model_Paylike::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('paylike_payment')->__('Instant') //Authorize and Capture
            ),
        );
    }

}
