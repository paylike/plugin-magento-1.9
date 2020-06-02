<?php

class Paylike_Payment_Model_Paylike extends Mage_Payment_Model_Method_Abstract {

	const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
	const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';

	protected $_isGateway = true;
	protected $_canAuthorize = true;
	protected $_canUseCheckout = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = false;
	protected $_canRefund = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid = true;
	protected $_code = 'paylike';
	protected $_formBlockType = 'paylike_payment/form_paylike';
	protected $_infoBlockType = 'paylike_payment/info_paylike';

	private $client;

	private $storeId = null;

	/**
	 * @throws Varien_Exception
	 */
	public function _construct() {
		parent::_construct();

		$this->_init( 'paylike_payment/paylike' );
	}

	/**
	 * @param Varien_Object $payment
	 * @param float         $amount
	 * @param bool          $ajax
	 *
	 * @return $this|Mage_Payment_Model_Abstract
	 * @throws Mage_Core_Exception
	 * @throws Varien_Exception
	 */
	public function authorize( Varien_Object $payment, $amount, $ajax = false ) {
		$order_id = $payment->getOrder()->getId();
		/** @var Mage_Sales_Model_Order $order */
		$order = $payment->getOrder();
		Mage::log( '------------- Start payment --------------' . PHP_EOL . "Info: Begin processing payment for order $order_id for the amount of {$order->getGrandTotal()}." . PHP_EOL . 'Transaction id:' . $payment->getPaylikeTransactionId() . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, Zend_Log::DEBUG, 'paylike.log' );

		/** The transaction id is a getter from the request, paylike_transaction_id */
		$payment->setTransactionId( $payment->getPaylikeTransactionId() );
		$payment->setIsTransactionClosed( 0 );

		try {
			$transaction = $this->getClient()->transactions()->fetch( $payment->getPaylikeTransactionId() );
		} catch ( ApiException  $exception ) {
			$message = $this->handleExceptions( $exception, 'Issue: Authorization Failed!' );
			Mage::throwException( $message );

			return $this;
		}

		if ( ! $transaction['successful'] ) {
			Mage::log( '------------- Problem payment --------------' . PHP_EOL . json_encode( $transaction ) . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, Zend_Log::ERR, 'paylike.log' );
			Mage::throwException( Mage::helper( 'paylike_payment' )->__( 'There has been a problem, the transaction failed, try to pay again, or contact support' ) );
		}


		$data = array(
			'paylike_tid'     => $payment->getPaylikeTransactionId(),
			'order_id'        => $order_id,
			'payed_at'        => date( 'Y-m-d H:i:s' ),
			'payed_amount'    => $amount,
			'refunded_amount' => 0,
			'captured'        => 'NO'
		);

		$model = Mage::getModel( 'paylike_payment/paylikeadmin' );

		try {
			$model->setData( $data )
			      ->save();

			return $this;

		} catch ( Exception $e ) {

			$errormsg = Mage::helper( 'paylike_payment' )->__( $e->getMessage() );
			Mage::throwException( $errormsg );

		}

		return $this;
	}

	/**
	 * @param Varien_Object $payment
	 * @param               $amount
	 *
	 * @return $this|Mage_Payment_Model_Abstract
	 * @throws Mage_Core_Exception
	 * @throws Varien_Exception
	 */
	public function capture( Varien_Object $payment, $amount ) {

		if ( ! $payment->getLastTransId() ) {
			$payment->setLastTransId( $payment->getPaylikeTransactionId() );
		}
		/** @var Mage_Sales_Model_Order $order */
		$order = $payment->getOrder();

		if ( $order->getOrderCurrencyCode() != $order->getBaseCurrencyCode() ) {
			if ( $amount != $order->getBaseGrandTotal() ) {
				Mage::throwException( 'This order has been paid with ' . $order->getOrderCurrencyCode() . ' while the store base currency was ' . $order->getBaseCurrencyCode() . '. Because of that you cannot capture a different amount than the base amount. This is due to the fact that the full base amount corresponds directly with the full order amount, in the currency the customer paid. The main reason is that an accurate conversion is not possible.' );

				return $this;
			}
			$amount = $payment->getAmountAuthorized();
		}

		$order_id        = $order->getId();
		$real_order_id   = $order->getRealOrderId();
		$currency_code   = $order->getOrderCurrencyCode();
		$client          = $this->getClient(); // load the autoloader
		$currencyManager = new Paylike_Data_Currencies();
		$arr             = array(
			'currency'   => $currency_code,
			'descriptor' => $this->getDescriptor( "#" . $real_order_id ),
			'amount'     => $currencyManager->ceil( $amount, $currency_code ),
		);
		Mage::log( '------------- Start capture --------------' . PHP_EOL . "Info: Begin capturing payment for order $order_id for the amount of {$arr['amount']}. Currency: {$arr['currency']}" . PHP_EOL . 'Transaction id:' . $payment->getLastTransId() . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, Zend_Log::DEBUG, 'paylike.log' );

		try {
			$transaction = $this->getClient()->transactions()->capture( $payment->getLastTransId(), $arr );
		} catch ( ApiException  $exception ) {
			$message = $this->handleExceptions( $exception, 'Issue: Authorization Failed!' );
			Mage::throwException( $message );

			return $this;
		}

		Mage::log( 'Capture' . json_encode( $transaction ), false, 'paylike.log' );

		if ( ! $transaction['successful'] ) {
			Mage::log( '------------- Problem payment --------------' . PHP_EOL . json_encode( $transaction ) . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, Zend_Log::ERR, 'paylike.log' );
			Mage::throwException( 'Capture has failed, this may be a problem with your configuration, or with the server. Check your configuration and try again. Key used:' . $this->getApiKey() );
		}

		// store in admin table
		$paylike_admin = Mage::getModel( 'paylike_payment/paylikeadmin' )
		                     ->getCollection()
		                     ->addFieldToFilter( 'paylike_tid', $payment->getPaylikeTransactionId() )
		                     ->addFieldToFilter( 'order_id', $order_id )
		                     ->getFirstItem()
		                     ->getData();

		$payment->setTransactionId( $payment->getPaylikeTransactionId() );
		$payment->setIsTransactionClosed( 1 );

		if ( empty( $paylike_admin ) ) {
			$data  = array(
				'paylike_tid'     => $payment->getPaylikeTransactionId(),
				'order_id'        => $order_id,
				'payed_at'        => date( 'Y-m-d H:i:s' ),
				'payed_amount'    => $amount,
				'refunded_amount' => 0,
				'captured'        => 'YES'
			);
			$model = Mage::getModel( 'paylike_payment/paylikeadmin' );
			$model->setData( $data )
			      ->save();


		} else {
			$id   = $paylike_admin['id'];
			$data = array(
				'captured' => 'YES'
			);

			$model = Mage::getModel( 'paylike_payment/paylikeadmin' );

			try {
				$model->load( $id )
				      ->addData( $data )
				      ->setId( $id )
				      ->save();

				return $this;

			} catch ( Exception $e ) {

				$errormsg = Mage::helper( 'paylike_payment' )->__( $e->getMessage() );
				Mage::throwException( $errormsg );

			}
		}


		return $this;
	}

	/**
	 * @param Varien_Object $payment
	 * @param float         $amount
	 *
	 * @return $this|array|Mage_Payment_Model_Abstract
	 * @throws Mage_Core_Exception
	 * @throws Varien_Exception
	 */
	public function refund( Varien_Object $payment, $amount ) {
		/** @var Mage_Sales_Model_Order $order */
		$order = $payment->getOrder();

		$real_order_id = $order->getRealOrderId();
		if ( $order->getOrderCurrencyCode() != $order->getBaseCurrencyCode() ) {
			if ( $amount != $order->getBaseGrandTotal() ) {
				Mage::throwException( 'This order has been paid with ' . $order->getOrderCurrencyCode() . ' while the store base currency was ' . $order->getBaseCurrencyCode() . '. Because of that you cannot refund a different amount than the base amount. This is due to the fact that the full base amount corresponds directly with the full order amount, in the currency the customer paid. The main reason is that an accurate conversion is not possible.' );

				return $this;
			}

			$amount = $payment->getAmountAuthorized();
		}
		$order_id      = $payment->getOrder()->getId();
		$currency_code = $order->getOrderCurrencyCode();

		$client          = $this->getClient(); // load the autoloader
		$currencyManager = new Paylike_Data_Currencies();
		$arr             = array(
			'descriptor' => $this->getDescriptor( '#' . $real_order_id ),
			'amount'     => $currencyManager->ceil( $amount, $currency_code )
		);
		Mage::log( '------------- Start refund --------------' . PHP_EOL . "Info: Begin refund payment for order $order_id for the amount of {$arr['amount']}." . PHP_EOL . 'Transaction id:' . $payment->getLastTransId() . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, Zend_Log::DEBUG, 'paylike.log' );
		try {
			$transaction = $this->getClient()->transactions()->refund( $payment->getLastTransId(), $arr );
		} catch ( ApiException  $exception ) {
			$message = $this->handleExceptions( $exception, 'Issue: Refund Failed!' );
			Mage::throwException( $message );

			return $this;
		}

		Mage::log( 'Refund' . json_encode( $transaction ), Zend_Log::DEBUG, 'paylike.log' );

		if ( ! $transaction['successful'] ) {
			Mage::log( '------------- Problem refund --------------' . PHP_EOL . json_encode( $transaction ) . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, Zend_Log::ERR, 'paylike.log' );
			Mage::throwException( 'Refund has failed, this may be a problem with your configuration, or with the server. Check your configuration and try again. Key used:' . $this->getApiKey() );
		}


		return $this;
	}

	/**
	 * @param Varien_Object $payment
	 *
	 * @return Paylike_Payment_Model_Paylike
	 * @throws Mage_Core_Exception
	 * @throws Varien_Exception
	 */
	public function void( Varien_Object $payment ) {

		$order = $payment->getOrder();

		$order_id        = $payment->getOrder()->getId();
		$currency_code   = $order->getOrderCurrencyCode();
		$amount          = $payment->getAmountAuthorized();
		$client          = $this->getClient(); // load the autoloader
		$currencyManager = new Paylike_Data_Currencies();
		$arr             = array(
			'amount' => $currencyManager->ceil( $amount, $currency_code )
		);
		Mage::log( '------------- Start void --------------' . PHP_EOL . "Info: Begin void payment for order $order_id for the amount of {$arr['amount']}." . PHP_EOL . 'Transaction id:' . $payment->getLastTransId() . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, Zend_Log::DEBUG, 'paylike.log' );
		try {
			$transaction = $this->getClient()->transactions()->void( $payment->getLastTransId(), $arr );
		} catch ( ApiException  $exception ) {
			$message = $this->handleExceptions( $exception, 'Issue: Void Failed!' );
			Mage::throwException( $message );

			return $this;
		}

		Mage::log( 'Void' . json_encode( $transaction ), Zend_Log::DEBUG, 'paylike.log' );

		if ( ! $transaction['successful'] ) {
			Mage::log( '------------- Problem Void --------------' . PHP_EOL . json_encode( $transaction ) . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, Zend_Log::ERR, 'paylike.log' );
			Mage::throwException( 'Void has failed, this may be a problem with your configuration, or with the server. Check your configuration and try again. Key used:' . $this->getApiKey() );
		}
	}

	/**
	 * @param null $quote
	 *
	 * @return bool|mixed
	 */
	public function isAvailable( $quote = null ) {
		return Mage::getStoreConfig( 'payment/paylike/status' );
	}

	/**
	 * @param mixed $data
	 *
	 * @return $this|Mage_Payment_Model_Info
	 * @throws Varien_Exception
	 */
	public function assignData( $data ) {
		if ( ! ( $data instanceof Varien_Object ) ) {
			$data = new Varien_Object( $data );
		}
		$info = $this->getInfoInstance();
		$info->setPaylikeTransactionId( $data->getPaylikeTransactionId() );

		return $this;
	}

	/**
	 * @return $this|Mage_Payment_Model_Abstract
	 * @throws Mage_Core_Exception
	 * @throws Varien_Exception
	 */
	public function validate() {
		$info = $this->getInfoInstance();
		if ( $info->getPaylikeTransactionId() == null ) {
			$errorMsg = false;
			Mage::throwException( $errorMsg );
		}

		return $this;
	}

	/**
	 * @return mixed
	 */
	protected function getApiKey() {
		if ( $this->getPaymentMode() == 'test' ) {
			return $this->getConfigData( 'test_api_key' );
		} else {
			return $this->getConfigData( 'live_api_key' );
		}
	}

	/**
	 * @return mixed
	 */
	protected function getPublicKey() {
		if ( $this->getPaymentMode() == 'test' ) {
			return $this->getConfigData( 'test_public_key' );
		} else {
			return $this->getConfigData( 'live_public_key' );
		}
	}

	/**
	 * @return mixed
	 */
	protected function getPaymentMode() {
		return $this->getConfigData( 'payment_mode' );
	}

	/* protected  function getPopupTitle()
	  {
	  return Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
  } */

	/**
	 * @return bool
	 */
	public function canRefund() {
		return $this->_canRefund;
	}

	/**
	 * @param Varien_Object $payment
	 *
	 * @return bool
	 */
	public function canVoid( Varien_Object $payment ) {
		return $this->_canVoid;
	}

	/**
	 * @return string
	 */
	public function getModuleCode() {
		return $this->_code;
	}

	/**
	 * Get the key of the global merchant descriptor
	 * @throws Mage_Core_Exception
	 */
	protected function getGlobalMerchantDescriptor() {
		Mage::log( 'Info: Attempting to fetch the global merchant id ' . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__, false, 'paylike.log' );
		try {
			$identity = $this->getClient()->apps()->fetch();
		} catch ( Paylike_Exception_ApiException $exception ) {
			$error = Mage::helper( 'paylike_payment' )->__( "The private key doesn't seem to be valid", 'woocommerce-gateway-paylike' );

			Mage::throwException( $error );

			return false;
		}
		try {
			$merchants = $this->getClient()->merchants()->find( $identity['id'] );
			if ( $merchants ) {
				foreach ( $merchants as $merchant ) {
					if ( $this->getPaymentMode() == 'test' && $merchant['test'] && $merchant['key'] == $this->getPublicKey() ) {
						return $merchant['descriptor'];
					}
					if ( ! $merchant['test'] && $this->getPaymentMode() != 'test' && $merchant['key'] == $this->getPublicKey() ) {
						return $merchant['descriptor'];
					}
				}
			}
		} catch ( Paylike_Exception_ApiException $exception ) {
			$error = Mage::helper( 'paylike_payment' )->__( 'No valid merchant id was found', 'woocommerce-gateway-paylike' );

			Mage::throwException( $error );

			return false;
		}
	}

	/**
	 * Get account user descriptor and append text to it if needed
	 *
	 * @param $text_to_append
	 *
	 * @return bool|null|string|string[]
	 */
	protected function getDescriptor( $text_to_append ) {
		$descriptor = $this->getGlobalMerchantDescriptor();
		if ( ! $descriptor ) {
			return '';
		}
		if ( strlen( $descriptor ) + strlen( $text_to_append ) <= 22 ) {
			$descriptor = $descriptor . $text_to_append;
		}
		//remove non ascii chars
		$descriptor = preg_replace( '/^[\x20-\x7E]$/', '', $descriptor );

		return substr( $descriptor, 0, 22 );
	}

	/**
	 * Log exceptions.
	 *
	 * @param Paylike_Exception_ApiException $exception
	 * @param string                          $context
	 *
	 * @return bool|string
	 */
	public function handleExceptions( $exception, $context = '' ) {
		if ( ! $exception ) {
			return false;
		}
		$exception_type = get_class( $exception );
		$message        = '';
		switch ( $exception_type ) {
			case 'Paylike_Exception_NotFound':
				$message = Mage::helper( 'paylike_payment' )->__( 'Transaction not found! Check the transaction key used for the operation.' );
				break;
			case 'Paylike_Exception_InvalidRequest':
				$message = Mage::helper( 'paylike_payment' )->__( 'The request is not valid! Check if there is any validation bellow at the end of this message and adjust if possible, if not, and the problem persists, contact the developer.' );
				break;
			case 'Paylike_Exception_Forbidden':
				$message = Mage::helper( 'paylike_payment' )->__( 'The operation is not allowed! You do not have the rights to perform the operation, make sure you have all the grants required on your Paylike account.' );
				break;
			case 'Paylike_Exception_Unauthorized':
				$message = Mage::helper( 'paylike_payment' )->__( 'The operation is not properly authorized! Check the credentials set in settings for Paylike.' );
				break;
			case 'Paylike_Exception_Conflict':
				$message = Mage::helper( 'paylike_payment' )->__( 'The operation leads to a conflict! The same transaction is being requested for modification at the same time. Try again later.' );
				break;
			case 'Paylike_Exception_ApiConnection':
				$message = Mage::helper( 'paylike_payment' )->__( 'Network issues ! Check your connection and try again.' );
				break;
			case 'Paylike_Exception_ApiException':
				$message = Mage::helper( 'paylike_payment' )->__( 'There has been a server issue! If this problem persists contact the developer.' );
				break;
		}
		$message = Mage::helper( 'paylike_payment' )->__( 'Error: ' ) . $message;
		$message .= $exception->getMessage();
		Mage::logException( $exception );

		return $message;
	}

	/**
	 * @return Paylike_Paylike
	 */
	public function getClient() {
		if ( ! $this->client ) {
			$this->client = new Paylike_Paylike( $this->getApiKey() );
		}

		return $this->client;
	}


}
