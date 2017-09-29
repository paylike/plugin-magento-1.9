<?php

class Paylike_Payment_Model_Source_PaymentMode {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'test',
                'label' => Mage::helper('paylike_payment')->__('Test')
            ),
            array(
                'value' => 'live',
                'label' => Mage::helper('paylike_payment')->__('Live')
            ),
        );
    }

}
