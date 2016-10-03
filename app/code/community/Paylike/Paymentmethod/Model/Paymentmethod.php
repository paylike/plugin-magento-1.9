<?php
require_once dirname(__FILE__) . '/../Model/Paylike.php';

class Paylike_Paymentmethod_Model_Paymentmethod extends Mage_Payment_Model_Method_Cc
{
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canUseCheckout = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    protected $_code = 'paymentmethod';
    protected $_formBlockType = 'paymentmethod/form_paymentmethod';
    protected $_infoBlockType = 'paymentmethod/info_paymentmethod';

    public function authorize(Varien_Object $payment, $amount)
    {
        $payment->setTransactionId($payment->getPaylikeTransactionId());
        $payment->setIsTransactionClosed(0);
        return $this;

    }

    public function capture(Varien_Object $payment, $amount)
    {
        $arr = array('amount' => $amount,
            'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
            'descriptor' => 'Capture',
        );
        if ($amount <= 0) {
            Mage::throwException('Invalid amount for authorization.');
        }
        $paylike = new Paylike($this->getApiKey());
        if (!$payment->getLastTransId()) {
            $payment->setLastTransId($payment->getPaylikeTransactionId());
        }
        $result = $paylike->transactions->capture($payment->getLastTransId(), $arr);
        if ($result == false) {
            Mage::throwException('Transaction failed');
        }
        if ($result == true) {

            $payment->setTransactionId($payment->getPaylikeTransactionId());
            $payment->setIsTransactionClosed(1);
        }
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
        $arr = array('amount' => $amount,
            'descriptor' => 'refund',
        );
        if ($amount <= 0) {
            Mage::throwException('Invalid amount for authorization.');
        }
        $paylike = new Paylike($this->getApiKey());
        $result = $paylike->transactions->refund($payment->getLastTransId(), $arr);
        if ($result) {
            return parent::refund($payment, $amount);
        } else {
            Mage::throwException('Invalid transaction.');
        }

    }

    public function void(Varien_Object $payment)
    {
        $amount = floor($payment->getAmountAuthorized());
        $arr = array('amount' => $amount,
        );
        $paylike = new Paylike($this->getApiKey());
        $result = $paylike->transactions->voids($payment->getLastTransId(), $arr);
        if ($result) {
            return parent::void($payment);
        } else {
            Mage::throwException('Invalid Request.');
        }
    }

    protected function _place($payment, $amount, $requestType)
    {
        $payment->setAmount($amount);
        $request = $this->_buildRequest($payment);
        $this->_postRequest($request);

    }

    public function isAvailable($quote = null)
    {
        return Mage::getStoreConfig('payment/paymentmethod/active');
    }

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setPaylikeTransactionId($data->getPaylikeTransactionId());
        return $this;
    }

    public function validate()
    {
        $info = $this->getInfoInstance();
        if ($info->getPaylikeTransactionId() == null) {
            $errorMsg = false;
            Mage::throwException($errorMsg);
        }
        return $this;
    }

    protected function _getRequest()
    {
        $request = new Varien_Object();
        return $request;
    }

    protected function getMerchantId()
    {
        return Mage::getStoreConfig('payment/paymentmethod/merchant_id');
    }

    protected function getApiKey()
    {
        return Mage::getStoreConfig('payment/paymentmethod/api_key');
    }

    public function canRefund()
    {
        return $this->_canRefund;
    }

    public function canVoid(Varien_Object $payment)
    {
        return $this->_canVoid;
    }


}