<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Conf
*/
class Amasty_Conf_Model_Mysql4_Product_Attribute extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('amconf/product_attribute', 'product_attribute_id');
    }
}