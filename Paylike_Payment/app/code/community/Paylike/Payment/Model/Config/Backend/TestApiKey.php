<?php

class Paylike_Payment_Model_Config_Backend_TestApiKey extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $keysValidator = Mage::getModel( 'paylike_payment/config_validator_keys');

        $keysValidator->setApiKey($this->getValue())
            ->validateApiKey();

        return parent::save();
    }
}