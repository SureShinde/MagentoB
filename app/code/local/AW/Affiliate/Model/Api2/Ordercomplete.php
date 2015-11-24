<?php

/**
 * affiliate api resource
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Bilna Development Team <core@magentocommerce.com>
 */
class AW_Affiliate_Model_Api2_Ordercomplete extends Mage_Api2_Model_Resource
{    
    const DEFAULT_STORE_ID = 1;

    public function __construct() 
    {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }
}