<?php

class Paylike_Paymentgateway_Model_Source_PaymentLogos
{
    public function toOptionArray()
    {
        $paylike_logos = Mage::getModel('paymentgateway/paylikelogos')
            ->getCollection()
            ->getData();

        $logo_array = array();
        foreach($paylike_logos as $logo) {
            $data = array(
                'value' => $logo['file_name'],
                'label' => Mage::helper('paymentgateway')->__($logo['name'])
            );
            array_push($logo_array, $data);
        }

        return $logo_array;
    }
}

