<?php

class Paylike_Payment_Block_Form_Paylike extends Mage_Payment_Block_Form {

	/**
	 *
	 */
	protected function _construct() {
        parent::_construct();
        $this->setTemplate('paylike/form/paylike.phtml');
    }

}