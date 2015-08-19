<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productrelated_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Productrelated_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Productrelated_Rest {
    protected function _retrieveCollection() {
        $this->_checkVersion();
        
        $this->_getParams();
        $this->_getProduct();
        $this->_getBlocks();
        
        if (is_null($this->_blocks)) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $collection = $this->_getCollection();
        
        if (!$collection) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $result = array ();
        
        foreach ($collection as $k => $v) {
            $result[] = array (
                'entity_id' => $v->getData('entity_id'),
                'entity_type_id' => $v->getData('entity_type_id'),
                'attribute_set_id' => $v->getData('attribute_set_id'),
                'type_id' => $v->getData('type_id'),
                'sku' => $v->getData('sku'),
                'has_options' => $v->getData('has_options'),
                'required_options' => $v->getData('required_options'),
                'created_at' => $v->getData('created_at'),
                'updated_at' => $v->getData('updated_at'),
                'request_path' => $v->getData('request_path'),
                'price' => $v->getData('price'),
                'tax_class_id' => $v->getData('tax_class_id'),
                'final_price' => $v->getData('final_price'),
                'minimal_price' => $v->getData('minimal_price'),
                'min_price' => $v->getData('min_price'),
                'max_price' => $v->getData('max_price'),
                'tier_price' => $v->getData('tier_price'),
            );
        }
        
        return $result;
    }
}
