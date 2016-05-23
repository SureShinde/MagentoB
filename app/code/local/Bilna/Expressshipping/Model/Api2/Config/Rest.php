<?php
abstract class Bilna_Expressshipping_Model_Api2_Config_Rest extends Bilna_Expressshipping_Model_Api2_Config
{
    /**
     * Retrieve express shipping configuration attributes
     *
     * @return array
     */
    protected function _retrieve()
    {
        $enabled = Mage::getStoreConfig('bilna_expressshipping/status/enabled');
        $order_limit = Mage::getStoreConfig('bilna_expressshipping/orderlimit/limit');
        $allowed_paymethods = Mage::getStoreConfig('bilna_expressshipping/paymethod/allowed_paymethod');

        return array(
            'enabled' => $enabled, 
            'order_limit' => $order_limit,
            'allowed_paymethods' => $allowed_paymethods
        );
    }
}
