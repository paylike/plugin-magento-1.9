<?php

require_once dirname(__FILE__) . '/../Model/api/Client.php';

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

    public function _construct()
    {
        parent::_construct();
        $this->_init('paylike_payment/paylike');
    }

    public function authorize(Varien_Object $payment, $amount, $ajax = false)
    {

        $order = $payment->getOrder();
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'The API key is not valid.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('The API key is not valid.');
                Mage::throwException($errormsg);
            }
        }

        if ($amount <= 0) {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'Invalid amount for authorization.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('Invalid amount for authorization.');
                Mage::throwException($errormsg);
            }
        }

        $payment->setTransactionId($payment->getPaylikeTransactionId());
        $payment->setIsTransactionClosed(0);

        Paylike\Client::setKey($this->getApiKey());
        $fetch = Paylike\Transaction::fetch($payment->getTransactionId());
        $quoteId = Mage::helper('checkout/cart')->getQuote()->getId();

        if (is_array($fetch) && !empty($fetch['error']) && $fetch['error'] == 1) {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => $fetch['message']
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__($fetch['message']);
                Mage::throwException($errormsg);
            }
        } else {
            if (!empty($fetch['transaction'])) {
                $transaction = $fetch['transaction'];
                if ($transaction['amount'] != Mage::helper('paylike_payment/currencies')->Ceil($order->getGrandTotal(),$order->getOrderCurrencyCode()) || Mage::app()->getStore()->getCurrentCurrencyCode() != $transaction['currency'] || $transaction['custom']['quoteId'] != $quoteId) {
                    if ($ajax) {
                        $response = array(
                            'error' => 1,
                            'message' => 'The transaction is not valid.'
                        );
                        return $response;
                    } else {
                        $errormsg = Mage::helper('paylike_payment')->__('The transaction is not valid.');
                        Mage::throwException($errormsg);
                    }
                } else {
                    $order_id = $payment->getOrder()->getId();
                    $data = array(
                        'paylike_tid' => $payment->getPaylikeTransactionId(),
                        'order_id' => $order_id,
                        'payed_at' => date('Y-m-d H:i:s'),
                        'payed_amount' => $amount,
                        'refunded_amount' => 0,
                        'captured' => 'NO'
                    );

                    $model = Mage::getModel('paylike_payment/paylikeadmin');

                    try {
                        $model->setData($data)
                            ->save();
                        if ($ajax) {
                            $response = array(
                                'success' => 1,
                                'message' => 'The transaction was successfully fetched.'
                            );
                            return $response;
                        } else {
                            return $this;
                        }
                    } catch (Exception $e) {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => $e->getMessage()
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__($e->getMessage());
                            Mage::throwException($errormsg);
                        }
                    }
                }
            } else {
                if (!empty($fetch[0]['message'])) {
                    if ($ajax) {
                        $response = array(
                            'error' => 1,
                            'message' => $fetch[0]['message']
                        );
                        return $response;
                    } else {
                        $errormsg = Mage::helper('paylike_payment')->__($fetch[0]['message']);
                        Mage::throwException($errormsg);
                    }
                } else {
                    if ($ajax) {
                        $response = array(
                            'error' => 1,
                            'message' => 'The transaction is not valid.'
                        );
                        return $response;
                    } else {
                        $errormsg = Mage::helper('paylike_payment')->__('The transaction is not valid.');
                        Mage::throwException($errormsg);
                    }
                }
            }
        }
        return $this;
    }

    public function capture(Varien_Object $payment, $amount, $ajax = false)
    {
        if ($amount <= 0) {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'The amount is not valid for capture.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('The amount is not valid for capture.');
                Mage::throwException($errormsg);
            }
        }

        if (!$payment->getLastTransId()) {
            $payment->setLastTransId($payment->getPaylikeTransactionId());
        }

        //Authorize Order
        if (!$ajax) { //If it is not called from admin panel
            $this->authorize($payment, $amount);
        }
        $order = $payment->getOrder();
        $order_id = $order->getId();
        $real_order_id = $order->getRealOrderId();
        $paylike_admin = Mage::getModel('paylike_payment/paylikeadmin')
                ->getCollection()
                ->addFieldToFilter('paylike_tid', $payment->getPaylikeTransactionId())
                ->addFieldToFilter('order_id', $order_id)
                ->getFirstItem()
                ->getData();

        if (!empty($paylike_admin) && $paylike_admin['captured'] == 'NO') {
            $apiKey = $this->getApiKey();
            if (empty($apiKey)) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => 'The API key is not valid.'
                    );
                    return $response;
                } else {
                    $errormsg = Mage::helper('paylike_payment')->__('The API key is not valid.');
                    Mage::throwException($errormsg);
                }
            }
            Paylike\Client::setKey($this->getApiKey());
            $arr = array(
                'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'descriptor' => "Order #" . $real_order_id,
                'amount' => Mage::helper('paylike_payment/currencies')->Ceil($amount, Mage::app()->getStore()->getCurrentCurrencyCode()),
            );
            $capture = Paylike\Transaction::capture($payment->getLastTransId(), $arr);
            if (is_array($capture) && !empty($capture['error']) && $capture['error'] == 1) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => $capture['message']
                    );
                    return $response;
                } else {
                    $errormsg = Mage::helper('paylike_payment')->__($capture['message']);
                    Mage::throwException($errormsg);
                }
            } else {
                if (!empty($capture['transaction'])) {
                    $payment->setTransactionId($payment->getPaylikeTransactionId());
                    $payment->setIsTransactionClosed(1);

                    $id = $paylike_admin['id'];
                    $data = array(
                        'captured' => 'YES'
                    );

                    $model = Mage::getModel('paylike_payment/paylikeadmin');

                    try {
                        $model->load($id)
                            ->addData($data)
                            ->setId($id)
                            ->save();

                        if ($ajax) {
                            $response = array(
                                'success' => 1,
                                'message' => 'The transaction has successfully been captured.'
                            );
                            return $response;
                        } else {
                            return $this;
                        }
                    } catch (Exception $e) {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => $e->getMessage()
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__($e->getMessage());
                            Mage::throwException($errormsg);
                        }
                    }
                } else {
                    if (!empty($capture[0]['message'])) {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => $capture[0]['message']
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__($capture[0]['message']);
                            Mage::throwException($errormsg);
                        }
                    } else {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => 'The transaction has failed.'
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__('The transaction has failed.');
                            Mage::throwException($errormsg);
                        }
                    }
                }
            }
        } else if (!empty($paylike_admin) && $paylike_admin['captured'] == 'YES') {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'The order was already captured.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('The order was already captured.');
                Mage::throwException($errormsg);
            }
        } else {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'The transaction is not valid.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('The transaction is not valid.'.$payment->getPaylikeTransactionId());
                Mage::throwException($errormsg);
            }
        }
        return $this;
    }

    public function refund(Varien_Object $payment, $amount, $ajax = false)
    {
        $order_id = $payment->getOrder()->getId();
        $paylike_admin = Mage::getModel('paylike_payment/paylikeadmin')
                ->getCollection()
                ->addFieldToFilter('paylike_tid', $payment->getPaylikeTransactionId())
                ->addFieldToFilter('order_id', $order_id)
                ->getFirstItem()
                ->getData();

        if (!empty($paylike_admin) && $paylike_admin['captured'] == 'YES') {
            $apiKey = $this->getApiKey();
            if (empty($apiKey)) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => 'The API key is not valid.'
                    );
                    return $response;
                } else {
                    $errormsg = Mage::helper('paylike_payment')->__('The API key is not valid.');
                    Mage::throwException($errormsg);
                }
            }
            if ($amount <= 0) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => 'The amount you entered for refund is not valid.'
                    );
                    return $response;
                } else {
                    $errormsg = Mage::helper('paylike_payment')->__('The amount you entered for refund is not valid.');
                    Mage::throwException($errormsg);
                }
            }
            Paylike\Client::setKey($this->getApiKey());
            $arr = array(
                'descriptor' => '',
                'amount' => Mage::helper('paylike_payment/currencies')->Ceil($amount, $payment->getOrder()->getOrderCurrencyCode())
            );
            $refund = Paylike\Transaction::refund($payment->getLastTransId(), $arr);
            if (is_array($refund) && !empty($refund['error']) && $refund['error'] == 1) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => $refund['message']
                    );
                    return $response;
                } else {
                    $errormsg = Mage::helper('paylike_payment')->__($refund['message']);
                    Mage::throwException($errormsg);
                }
            } else {
                if (!empty($refund['transaction'])) {
                    $payment->setTransactionId($payment->getPaylikeTransactionId());

                    $id = $paylike_admin['id'];
                    $data = array(
                        'refunded_amount' => $paylike_admin['refunded_amount'] + $amount
                    );

                    $model = Mage::getModel('paylike_payment/paylikeadmin');

                    try {
                        $model->load($id)
                            ->addData($data)
                            ->setId($id)
                            ->save();

                        if ($ajax) {
                            $response = array(
                                'success' => 1,
                                'message' => 'The transaction has been refunded successfully.'
                            );
                            return $response;
                        } else {
                            return $this;
                        }
                    } catch (Exception $e) {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => $e->getMessage()
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__($e->getMessage());
                            Mage::throwException($errormsg);
                        }
                    }
                } else {
                    if (!empty($refund[0]['message'])) {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => $refund[0]['message']
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__($refund[0]['message']);
                            Mage::throwException($errormsg);
                        }
                    } else {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => 'The transaction is not valid.'
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__('The transaction is not valid.');
                            Mage::throwException($errormsg);
                        }
                    }
                }
            }
        } else if (!empty($paylike_admin) && $paylike_admin['captured'] == 'NO') {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'In order to refund you first need to capture the transaction.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('In order to refund you first need to capture the transaction.');
                Mage::throwException($errormsg);
            }
        } else {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'The transaction is not valid.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('The transaction is not valid.');
                Mage::throwException($errormsg);
            }
        }
        return $this;
    }

    public function void(Varien_Object $payment, $ajax = false)
    {
        $order_id = $payment->getOrder()->getId();
        $paylike_admin = Mage::getModel('paylike_payment/paylikeadmin')
                ->getCollection()
                ->addFieldToFilter('paylike_tid', $payment->getPaylikeTransactionId())
                ->addFieldToFilter('order_id', $order_id)
                ->getFirstItem()
                ->getData();

        if (!empty($paylike_admin) && $paylike_admin['captured'] == 'NO') {
            $amount = $payment->getAmountAuthorized();
            $arr = array(
                'amount' => Mage::helper('paylike_payment/currencies')->Ceil($amount, $payment->getOrder()->getOrderCurrencyCode()),
            );
            $apiKey = $this->getApiKey();
            if (empty($apiKey)) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => 'The API key is not valid.'
                    );
                    return $response;
                } else {
                    $errormsg = Mage::helper('paylike_payment')->__('The API key is not valid.');
                    Mage::throwException($errormsg);
                }
            }
            Paylike\Client::setKey($this->getApiKey());
            $void = Paylike\Transaction::void($payment->getLastTransId(), $arr);

            if (is_array($void) && !empty($void['error']) && $void['error'] == 1) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => $void['message'],
                    );
                    return $response;
                } else {
                    $errormsg = Mage::helper('paylike_payment')->__($void['message']);
                    Mage::throwException($errormsg);
                }
            } else {
                if (!empty($void['transaction'])) {
                    if ($ajax) {
                        $response = array(
                            'success' => 1,
                            'message' => 'The transaction has been successfully voided.',
                        );
                        return $response;
                    } else {
                        return $this;
                    }
                } else {
                    if (!empty($void[0]['message'])) {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => $void[0]['message'],
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__($void[0]['message']);
                            Mage::throwException($errormsg);
                        }
                    } else {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => 'The transaction is not valid.'
                            );
                            return $response;
                        } else {
                            $errormsg = Mage::helper('paylike_payment')->__('The transaction is not valid.');
                            Mage::throwException($errormsg);
                        }
                    }
                }
            }
        } else if (!empty($paylike_admin) && $paylike_admin['captured'] == 'YES') {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'You can\'t void the transaction because it has already been captured, you can only refund.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('You can\'t void the transaction because it has already been captured, you can only refund.');
                Mage::throwException($errormsg);
            }
        } else {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'The transaction is not valid.'
                );
                return $response;
            } else {
                $errormsg = Mage::helper('paylike_payment')->__('The transaction is not valid.');
                Mage::throwException($errormsg);
            }
        }
    }

    public function isAvailable($quote = null) {
        return Mage::getStoreConfig('payment/paylike/status');
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

    protected function getApiKey() {
        if (Mage::getStoreConfig('payment/paylike/payment_mode') == 'test') {
            return Mage::getStoreConfig('payment/paylike/test_api_key');
        } else {
            return Mage::getStoreConfig('payment/paylike/live_api_key');
        }
    }

    /* protected  function getPopupTitle()
      {
      return Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
      } */

    public function canRefund()
    {
        return $this->_canRefund;
    }

    public function canVoid(Varien_Object $payment)
    {
        return $this->_canVoid;
    }

    public function getModuleCode()
    {
        return $this->_code;
    }



}
