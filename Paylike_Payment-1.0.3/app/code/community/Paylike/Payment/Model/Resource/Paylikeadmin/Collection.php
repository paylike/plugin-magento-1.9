<?php

class Paylike_Payment_Model_Resource_Paylikeadmin_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected function _constuct() {
        $this->_init('paylike_payment/paylikeadmin');
    }

}
