<?php
/**
 * Description of Bilna_Netsuite_Model_Product
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Netsuite_Model_Product extends Mage_Core_Model_Abstract {
    public function getProductCollection() {
        $_collection = Mage::getModel('catalog/product')->getCollection()
            //->addAttributeToSelect('*');
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        return $_collection;
    }
}
