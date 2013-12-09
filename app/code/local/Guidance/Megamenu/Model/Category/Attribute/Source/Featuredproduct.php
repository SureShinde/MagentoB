<?php

/**
 * Catalog category featured product attribute source
 *
 * @author      Guidance Magento Team <magento@guidance.com>
 * @category    Guidance
 * @package     Megamenu
 * @copyright   Copyright 2013 Guidance Solutions (http://www.guidance.com)
 *
 */
class Guidance_Megamenu_Model_Category_Attribute_Source_Featuredproduct
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get list of all available products for category
     *
     * @return mixed
     */
    public function getAllOptions()
    {
        $category = Mage::registry('category');

        /* @var $products Mage_Catalog_Model_Resource_Product_Collection */
        $products  = Mage::getModel('catalog/product')->getCollection();
        if ($category->getId()) {
            $products->addCategoryFilter($category);
        }
        else {
            return array(
                array(
                    'value' =>  '',
                    'label' =>  'Assign products to category first',
                ));
        }
        $products->addAttributeToFilter("status", array("eq" => 1));
        $products->addAttributeToFilter("visibility", array("neq" => 1));
        $products->addAttributeToSort('name','ASC');
        $products->load();

        $options = array();

        foreach ($products as $product) {
            /* @var $product Mage_Catalog_Model_Product */
            $options[] = array(
                'value' => $product->getData('entity_id'),
                'label' => $product->getData('name'),
            );
        }
        $blankrow = array(
                        array(
                            'value' =>  '',
                            'label' =>  'Please select product',
                        ));

        if (count($options) < 1) {
            return array(
                array(
                    'value' =>  '',
                    'label' =>  'Assign products to category first',
                ));
        } else {
            $options_merge = array_merge($blankrow, $options);
        }
        return $options_merge;
    }
}
