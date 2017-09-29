<?php

class Paylike_Payment_Model_Resource_Paylikelogos extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct() {
        $this->_init('paylike_payment/paylikelogos', 'id');
    }

}
