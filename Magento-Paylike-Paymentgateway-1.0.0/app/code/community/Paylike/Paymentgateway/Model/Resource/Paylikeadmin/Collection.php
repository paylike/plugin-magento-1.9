<?php
class Paylike_Paymentgateway_Model_Resource_Paylikeadmin_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
    protected function _constuct(){
        $this->_init('paymentgateway/paylikeadmin');
    }
}