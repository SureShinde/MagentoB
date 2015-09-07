<?php
/**
 * Description of Bilna_Routes_Model_Api2
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Routes_Model_Api2 extends Mage_Api2_Model_Resource {
    const DEFAULT_STORE_ID = 1;
    
    protected $_data = array ();
    
    //- product collection
    protected $_attributeProductCollection = array ('entity_id', 'type_id', 'sku', 'name', 'url_key', 'url_path', 'special_price', 'status', 'visibility', 'price_type', 'price', 'price_view', 'special_from_date', 'special_to_date', 'news_from_date', 'news_to_date', 'group_price', 'tier_price', 'is_in_stock', 'is_salable', 'stock_data', 'attribute_set_id', 'short_description');
    
    public function __construct() {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }
    
    protected function _getStore() {
        return Mage::app()->getStore();
    }
}
