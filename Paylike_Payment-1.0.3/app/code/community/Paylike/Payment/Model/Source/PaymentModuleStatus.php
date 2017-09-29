<?php

class Paylike_Payment_Model_Source_PaymentModuleStatus {

    public function toOptionArray() {
        return array(
            array(
                'value' => 1,
                'label' => Mage::helper('paylike_payment')->__('Enabled')
            ),
            array(
                'value' => 0,
                'label' => Mage::helper('paylike_payment')->__('Disabled')
            ),
        );
    }

}
