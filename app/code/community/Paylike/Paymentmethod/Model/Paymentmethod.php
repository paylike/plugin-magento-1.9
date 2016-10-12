<?php

require_once dirname(__FILE__) . '/../Model/Paylike.php';

class Paylike_Paymentmethod_Model_Paymentmethod extends Mage_Payment_Model_Method_Cc
{
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
    protected $_code = 'paymentmethod';
    protected $_formBlockType = 'paymentmethod/form_paymentmethod';
    protected $_infoBlockType = 'paymentmethod/info_paymentmethod';

    public function authorize(Varien_Object $payment, $amount)
    {
        $payment->setTransactionId($payment->getPaylikeTransactionId());
        $payment->setIsTransactionClosed(0);
        $paylike = new Paylike($this->getApiKey());
        $result = $paylike->transactions->fetch($payment->getTransactionId());
        if ($transaction = $result->transaction) {
            if ($transaction->amount != $amount * 100 || Mage::app()->getStore()->getCurrentCurrencyCode() != $transaction->currency) {
                $errormsg = $this->__('Invalid transaction.');
                Mage::throwException($errormsg);
            }
        } else {
            $errormsg = $this->__('Invalid transaction.');
            Mage::throwException($errormsg);
        }
        return $this;		
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $paylike = new Paylike($this->getApiKey());
        $arr = array('amount' => $amount * 100,
            'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
        );
        if ($amount <= 0) {
            $errormsg = $this->__('Invalid amount for authorization.');
            Mage::throwException($errormsg);
        }
        if (!$payment->getLastTransId()) {
            $payment->setLastTransId($payment->getPaylikeTransactionId());
        }
        
        $result = $paylike->transactions->capture($payment->getLastTransId(), $arr);
        if ($result == false) {
            $errormsg = $this->__('Transaction failed');
            Mage::throwException($errormsg);
        } else {

            $payment->setTransactionId($payment->getPaylikeTransactionId());
            $payment->setIsTransactionClosed(1);
        }
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
        $arr = array('amount' => $amount * 100);
        if ($amount <= 0) {
            Mage::throwException($this->__('Invalid amount for authorization.'));
        }
        $paylike = new Paylike($this->getApiKey());
        $result = $paylike->transactions->refund($payment->getLastTransId(), $arr);
        if ($result) {
            return $this;
        } else {
            $errormsg = $this->__('Invalid transaction.');
            Mage::throwException($errormsg);
        }

    }

    public function void(Varien_Object $payment)
    {
        $amount = $payment->getAmountAuthorized();
        $arr = array('amount' => $amount * 100,
        );
        $paylike = new Paylike($this->getApiKey());
        $result = $paylike->transactions->voids($payment->getLastTransId(), $arr);
        if ($result) {
            return $this;
        } else {
            $errormsg = $this->__('Invalid Request.');
            Mage::throwException($errormsg);
        }
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
