<?php

class Paylike_Payment_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * @return mixed
	 */
	public function getPaymentMethodDescription() {
		return Mage::getStoreConfig( 'payment/paylike/description' );
	}

	/**
	 * @return string
	 */
	public function getAlertOnNotReady() {
		return ( Mage::helper( 'payment' )->__( 'The payment data is not ready, wait for all parts to be loaded.' ) );
	}

	/**
	 * @return mixed
	 */
	public function getPopupTitle() {
		return Mage::getStoreConfig( 'payment/paylike/pop_up_title' );
	}


	/**
	 * @return mixed
	 */
	public function getPublicKey() {
		if ( $this->getPaymentMode() == 'test' ) {
			return Mage::getStoreConfig( 'payment/paylike/test_public_key' );
		} else {
			return Mage::getStoreConfig( 'payment/paylike/live_public_key' );
		}
	}

	/**
	 * @return mixed
	 */
	public function getPaymentMode() {
		return Mage::getStoreConfig( 'payment/paylike/payment_mode' );
	}

	/**
	 * @param bool $json
	 *
	 * @return array
	 * @throws Varien_Exception
	 */
	public function getProducts( $json = false ) {
		$products_array = array();
		$products       = Mage::getSingleton( 'checkout/session' )->getQuote()->getAllVisibleItems();
		foreach ( $products as $product ) {
			$name             = $product->getData( 'name' );
			$products_array[] = array(
				'ID'       => $product->getData( 'item_id' ),
				'Name'     => str_replace( "'", "&#39;", $name ),
				'Quantity' => $product->getData( 'qty' )
			);
		}
		if ( $json ) {
			$products_array = Mage::helper( 'core' )->jsonEncode( $products_array );
		}

		return $products_array;
	}

	/**
	 * @return mixed
	 */
	public function getTelephone() {
		return Mage::helper( 'checkout/cart' )->getQuote()->getShippingAddress()->getData( 'telephone' );
	}

	/**
	 * @return mixed|string
	 */
	public function getAddress() {
		$customer_address = Mage::helper( 'checkout/cart' )->getQuote()->getShippingAddress()->getData();
		$street           = trim( preg_replace( '/\s+/', ' ', $customer_address['street'] ) );
		$city             = $customer_address['city'];
		$region           = $customer_address['region'];
		$country_code     = $customer_address['country_id'];
		$country          = Mage::app()->getLocale()->getCountryTranslation( $country_code );
		$postcode         = $customer_address['postcode'];
		$address          = $street . ', ' . $city . ', ' . $region . ', ' . $country . ', ' . $postcode;
		$address          = str_replace( ', ,', ',', $address );

		return $address;
	}

	/**
	 * @param bool $json
	 *
	 * @return mixed
	 */
	public function getCreditCardLogos( $json = false ) {
		if ( $json ) {
			$logos       = Mage::getStoreConfig( 'payment/paylike/payment_logo' );
			$logos_array = explode( ',', $logos );

			return Mage::helper( 'core' )->jsonEncode( $logos_array );
		} else {
			return Mage::getStoreConfig( 'payment/paylike/payment_logo' );
		}
	}

}
