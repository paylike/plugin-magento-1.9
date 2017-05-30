<?php

require_once dirname(__FILE__) . '/../Model/api/Client.php';

class Paylike_Paymentgateway_Model_Paymentgateway extends Mage_Payment_Model_Method_Abstract
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
    protected $_code = 'paymentgateway';
    protected $_formBlockType = 'paymentgateway/form_paymentgateway';
    protected $_infoBlockType = 'paymentgateway/info_paymentgateway';

    public function _construct()
    {
        parent::_construct();
        $this->_init('paymentgateway/paymentgateway');
    }

    public function authorize(Varien_Object $payment, $amount, $ajax=false)
    {
        if(empty($this->getApiKey())) {
            if($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'Invalid API key.'
                );
                return $response;
            } else {
                $errormsg = $this->__('Invalid API key.');
                Mage::throwException($errormsg);
            }
        }

        if ($amount <= 0) {
            if($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'Invalid amount for authorization.'
                );
                return $response;
            } else {
                $errormsg = $this->__('Invalid amount for authorization.');
                Mage::throwException($errormsg);
            }
        }

        $payment->setTransactionId($payment->getPaylikeTransactionId());
        $payment->setIsTransactionClosed(0);

        Paylike\Client::setKey($this->getApiKey());
        $fetch = Paylike\Transaction::fetch( $payment->getTransactionId() );
        $quoteId = Mage::helper('checkout/cart')->getQuote()->getId();

        if (is_array($fetch) && !empty($fetch['error']) && $fetch['error'] == 1) {
            if($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => $fetch['message']
                );
                return $response;
            } else {
                $errormsg = $this->__($fetch['message']);
                Mage::throwException($errormsg);
            }

        } else {
            if (!empty($fetch['transaction'])) {
                $transaction = $fetch['transaction'];
                if ($transaction['amount'] != $amount * 100 || Mage::app()->getStore()->getCurrentCurrencyCode() != $transaction['currency'] || $transaction['custom']['quoteId'] != $quoteId) {
                    if($ajax) {
                        $response = array(
                            'error' => 1,
                            'message' => 'Invalid transaction.'
                        );
                        return $response;
                    } else {
                        $errormsg = $this->__('Invalid transaction.');
                        Mage::throwException($errormsg);
                    }
                } else {
                    $order_id = $payment->getOrder()->getId();
                    $data = array(
                        'paylike_tid' => $payment->getPaylikeTransactionId(),
                        'order_id' => $order_id,
                        'payed_at'    => date('Y-m-d H:i:s'),
                        'payed_amount' => $amount,
                        'refunded_amount' => 0,
                        'captured' => 'NO'
                    );

                    $model = Mage::getModel('paymentgateway/paylikeadmin');

                    try {
                        $model->setData($data)
                            ->save();
                        if ($ajax) {
                            $response = array(
                                'success' => 1,
                                'message' => 'Transaction successfully Fetched.'
                            );
                            return $response;
                        } else {
                            return $this;
                        }

                    } catch (Exception $e) {
                        if($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => $e->getMessage()
                            );
                            return $response;
                        } else {
                            $errormsg = $this->__($e->getMessage());
                            Mage::throwException($errormsg);
                        }
                    }
                }
            } else {
                if(!empty($fetch[0]['message'])) {
                    if($ajax) {
                        $response = array(
                            'error' => 1,
                            'message' => $fetch[0]['message']
                        );
                        return $response;
                    } else {
                        $errormsg = $this->__($fetch[0]['message']);
                        Mage::throwException($errormsg);
                    }

                } else {
                    if($ajax) {
                        $response = array(
                            'error' => 1,
                            'message' => 'Invalid transaction.'
                        );
                        return $response;
                    } else {
                        $errormsg = $this->__('Invalid transaction.');
                        Mage::throwException($errormsg);
                    }
                }
            }
        }
        return $this;
    }

    public function capture(Varien_Object $payment, $amount, $ajax=false)
    {
        if ($amount <= 0) {
            if($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'Invalid amount for capture.'
                );
                return $response;
            } else {
                $errormsg = $this->__('Invalid amount for capture.');
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

        $order_id = $payment->getOrder()->getId();
        $real_order_id = $payment->getOrder()->getRealOrderId();
        $paylike_admin = Mage::getModel('paymentgateway/paylikeadmin')
            ->getCollection()
            ->addFieldToFilter('paylike_tid', $payment->getPaylikeTransactionId())
            ->addFieldToFilter('order_id', $order_id)
            ->getFirstItem()
            ->getData();

        if(!empty($paylike_admin) && $paylike_admin['captured'] == 'NO') {
            if(empty($this->getApiKey())) {
                if($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => 'Invalid API key.'
                    );
                    return $response;
                } else {
                    $errormsg = $this->__('Invalid API key.');
                    Mage::throwException($errormsg);
                }
            }
            Paylike\Client::setKey($this->getApiKey());
            $arr = array(
                'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'descriptor' => "Order #".$real_order_id,
                'amount' => $amount * 100,
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
                    $errormsg = $this->__($capture['message']);
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

                    $model = Mage::getModel('paymentgateway/paylikeadmin');

                    try {
                        $model->load($id)
                            ->addData($data)
                            ->setId($id)
                            ->save();

                        if ($ajax) {
                            $response = array(
                                'success' => 1,
                                'message' => 'Transaction successfully Captured.'
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
                            $errormsg = $this->__($e->getMessage());
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
                            $errormsg = $this->__($capture[0]['message']);
                            Mage::throwException($errormsg);
                        }

                    } else {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => 'Transaction failed.'
                            );
                            return $response;
                        } else {
                            $errormsg = $this->__('Transaction failed.');
                            Mage::throwException($errormsg);
                        }
                    }
                }
            }
        } else if(!empty($paylike_admin) && $paylike_admin['captured'] == 'YES') {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'Order already captured.'
                );
                return $response;
            } else {
                $errormsg = $this->__('Order already captured.');
                Mage::throwException($errormsg);
            }
        } else {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'Invalid transaction.'
                );
                return $response;
            } else {
                $errormsg = $this->__('Invalid transaction.');
                Mage::throwException($errormsg);
            }
        }
        return $this;
    }

    public function refund(Varien_Object $payment, $amount, $paylike_refund_reason='', $ajax=false)
    {
        $order_id = $payment->getOrder()->getId();
        $paylike_admin = Mage::getModel('paymentgateway/paylikeadmin')
            ->getCollection()
            ->addFieldToFilter('paylike_tid', $payment->getPaylikeTransactionId())
            ->addFieldToFilter('order_id', $order_id)
            ->getFirstItem()
            ->getData();

        if(!empty($paylike_admin) && $paylike_admin['captured'] == 'YES') {
            if(empty($this->getApiKey())) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => 'Invalid API key.'
                    );
                    return $response;
                } else {
                    $errormsg = $this->__('Invalid API key.');
                    Mage::throwException($errormsg);
                }
            }
            if ($amount <= 0) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => 'Invalid amount for refund.'
                    );
                    return $response;
                } else {
                    $errormsg = $this->__('Invalid amount for refund.');
                    Mage::throwException($errormsg);
                }
            }
            Paylike\Client::setKey($this->getApiKey());
            $arr = array(
                'descriptor' => $paylike_refund_reason,
                'amount' => $amount * 100
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
                    $errormsg = $this->__($refund['message']);
                    Mage::throwException($errormsg);
                }

            } else {
                if (!empty($refund['transaction'])) {
                    $payment->setTransactionId($payment->getPaylikeTransactionId());

                    $id = $paylike_admin['id'];
                    $data = array(
                        'refunded_amount' => $paylike_admin['refunded_amount'] + $amount
                    );

                    $model = Mage::getModel('paymentgateway/paylikeadmin');

                    try {
                        $model->load($id)
                            ->addData($data)
                            ->setId($id)
                            ->save();

                        if ($ajax) {
                            $response = array(
                                'success' => 1,
                                'message' => 'Transaction successfully Refunded.'
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
                            $errormsg = $this->__($e->getMessage());
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
                            $errormsg = $this->__($refund[0]['message']);
                            Mage::throwException($errormsg);
                        }

                    } else {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => 'Invalid transaction.'
                            );
                            return $response;
                        } else {
                            $errormsg = $this->__('Invalid transaction.');
                            Mage::throwException($errormsg);
                        }
                    }
                }
            }
        } else if(!empty($paylike_admin) && $paylike_admin['captured'] == 'NO') {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'You need to Captured Transaction prior to Refund.'
                );
                return $response;
            } else {
                $errormsg = $this->__('You need to Captured Transaction prior to Refund.');
                Mage::throwException($errormsg);
            }
        } else {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'Invalid transaction.'
                );
                return $response;
            } else {
                $errormsg = $this->__('Invalid transaction.');
                Mage::throwException($errormsg);
            }
        }
        return $this;
    }

    public function void(Varien_Object $payment, $ajax=false)
    {
        $order_id = $payment->getOrder()->getId();
        $paylike_admin = Mage::getModel('paymentgateway/paylikeadmin')
            ->getCollection()
            ->addFieldToFilter('paylike_tid', $payment->getPaylikeTransactionId())
            ->addFieldToFilter('order_id', $order_id)
            ->getFirstItem()
            ->getData();

        if(!empty($paylike_admin) && $paylike_admin['captured'] == 'NO') {
            $amount = $payment->getAmountAuthorized();
            $arr = array(
                'amount' => $amount * 100,
            );
            if(empty($this->getApiKey())) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => 'Invalid API key.'
                    );
                    return $response;
                } else {
                    $errormsg = $this->__('Invalid API key.');
                    Mage::throwException($errormsg);
                }
            }
            Paylike\Client::setKey($this->getApiKey());
            $void = Paylike\Transaction::void( $payment->getLastTransId(), $arr );

            if (is_array($void) && !empty($void['error']) && $void['error'] == 1) {
                if ($ajax) {
                    $response = array(
                        'error' => 1,
                        'message' => $void['message'],
                    );
                    return $response;
                } else {
                    $errormsg = $this->__($void['message']);
                    Mage::throwException($errormsg);
                }

            } else {
                if (!empty($void['transaction'])) {
                    if ($ajax) {
                        $response = array(
                            'success' => 1,
                            'message' => 'Transaction successfully Voided.',
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
                            $errormsg = $this->__($void[0]['message']);
                            Mage::throwException($errormsg);
                        }
                    } else {
                        if ($ajax) {
                            $response = array(
                                'error' => 1,
                                'message' => 'Invalid transaction.'
                            );
                            return $response;
                        } else {
                            $errormsg = $this->__('Invalid transaction.');
                            Mage::throwException($errormsg);
                        }
                    }
                }
            }
        } else if(!empty($paylike_admin) && $paylike_admin['captured'] == 'YES') {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'You can\'t Void transaction now . It\'s already Captured, try to Refund.'
                );
                return $response;
            } else {
                $errormsg = $this->__('You can\'t Void transaction now . It\'s already Captured, try to Refund.');
                Mage::throwException($errormsg);
            }
        } else {
            if ($ajax) {
                $response = array(
                    'error' => 1,
                    'message' => 'Invalid transaction.'
                );
                return $response;
            } else {
                $errormsg = $this->__('Invalid transaction.');
                Mage::throwException($errormsg);
            }
        }

    }

    public function isAvailable($quote = null)
    {
        return Mage::getStoreConfig('payment/paymentgateway/status');
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
        if(Mage::getStoreConfig('payment/paymentgateway/payment_mode') == 'test') {
            return Mage::getStoreConfig('payment/paymentgateway/test_api_key');
        } else {
            return Mage::getStoreConfig('payment/paymentgateway/live_api_key');
        }
    }

    /*protected  function getPopupTitle()
    {
         return Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
    }*/

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
