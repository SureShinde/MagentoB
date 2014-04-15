<?php

class Bilna_Megamenu_Model_Catalog_Category_Attribute_Source_Megamenu extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
    public function getAllOptions() {
        $result = array ();
        $options = Mage::getResourceModel('cms/block_collection')->load()->toOptionArray();
        array_unshift($options, array (
            'value' => '',
            'label' => Mage::helper('catalog')->__('Please select a static block ...')
        ));
        
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $option) {
                $result[$option['value']] = $option['label'];
            }
        }
        
        return $result;
    }
}
