<?php

class Paylike_Paymentgateway_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getPaymentMethodDescription()
    {
        return Mage::getStoreConfig('payment/paymentgateway/description');
    }

    public function getPopupTitle()
    {
        return Mage::getStoreConfig('payment/paymentgateway/pop_up_title');
    }

    public function getPopupDescription()
    {
        if (Mage::getStoreConfig('payment/paymentgateway/show_pop_up_description'))
            return Mage::getStoreConfig('payment/paymentgateway/pop_up_description');
        else {
            $products_label = array();
            $products = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
            foreach ($products as $product) {
                $products_label[] = $product->getData('qty') . 'x ' . str_replace("'", "\'", $product->getData('name'));
            }
            return implode(", & ", $products_label);
        }
    }

    public function getPublicKey()
    {
        if ($this->getPaymentMode() == 'test') {
            return Mage::getStoreConfig('payment/paymentgateway/test_public_key');
        } else {
            return Mage::getStoreConfig('payment/paymentgateway/live_public_key');
        }
    }

    public function getPaymentMode()
    {
        return Mage::getStoreConfig('payment/paymentgateway/payment_mode');
    }

    public function getProducts($json = false)
    {
        $products_array = array();
        $products = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
        foreach ($products as $product) {
            $name = $product->getData('name');
            $products_array[] = array(
                'ID' => $product->getData('item_id'),
                'Name' => str_replace("'", "&#39;", $name),
                'Quantity' => $product->getData('qty')
            );
        }
        if ($json)
            $products_array = Mage::helper('core')->jsonEncode($products_array);

        return $products_array;
    }

    public function getTelephone()
    {
        return Mage::helper('checkout/cart')->getQuote()->getShippingAddress()->getData('telephone');
    }

    public function getAddress()
    {
        $customer_address = Mage::helper('checkout/cart')->getQuote()->getShippingAddress()->getData();
        $street = trim(preg_replace('/\s+/', ' ', $customer_address['street']));
        $city = $customer_address['city'];
        $region = $customer_address['region'];
        $country_code = $customer_address['country_id'];
        $country = Mage::app()->getLocale()->getCountryTranslation($country_code);
        $postcode = $customer_address['postcode'];
        $address = $street . ', ' . $city . ', ' . $region . ', ' . $country . ', ' . $postcode;
        $address = str_replace(', ,', ',', $address);
        return $address;
    }

    public function getCreditCardLogos($json = false)
    {
        if ($json) {
            $logos = Mage::getStoreConfig('payment/paymentgateway/payment_logo');
            $logos_array = explode(',', $logos);
            return Mage::helper('core')->jsonEncode($logos_array);
        } else {
            return Mage::getStoreConfig('payment/paymentgateway/payment_logo');
        }
    }

}
