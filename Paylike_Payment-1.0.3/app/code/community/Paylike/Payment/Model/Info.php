<?php
exit;
class Paylike_Payment_Model_Info extends Mage_Payment_Model_Info {

    public function getMethodInstance() {exit;
        if (!$this->hasMethodInstance()) {
            if ($this->getMethod()) {
                echo $this->getMethod();exit;
                 if($this->getMethod() == 'paymentgateway')
                    $this->setMethod('paylike');
                $instance = Mage::helper('payment')->getMethodInstance($this->getMethod());
                if ($instance) {
                    $instance->setInfoInstance($this);
                    $this->setMethodInstance($instance);
                    return $instance;
                }
            }
            Mage::throwException(Mage::helper('payment')->__('The requested Payment Method is not available.'));
        }

        return $this->_getData('method_instance');
    }

}
