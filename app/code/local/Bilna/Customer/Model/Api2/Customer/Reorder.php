<?php
/**
 * Description of Bilnacredit
 *
 * @author Bilna Development Team <development@bilna.com>
 * 
 * @link http://mandagreen.com/showing-all-reviews-and-ratings-on-a-page-in-magento/
 * @link https://wiki.magento.com/display/m1wiki/Using+Magento+1.x+collections
 * @link http://devdocs.magento.com/guides/m1x/magefordev/mage-for-dev-8.html
 * 
 */

class Bilna_Customer_Model_Api2_Customer_Reorder extends Mage_Api2_Model_Resource
{    
    const DEFAULT_STORE_ID = 1;

    public function __construct() 
    {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }
}
