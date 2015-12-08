<?php

class Bilna_Fraud_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('bilna_fraud/log', 'log_id');
    }
}
