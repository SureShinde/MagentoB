<?php
/**
 * @author      Guidance Magento Team <magento@guidance.com>
 * @category    Guidance
 * @package     Megamenu
 * @copyright   Copyright (c) 2013 Guidance Solutions (http://www.guidance.com)
 */

class Guidance_Megamenu_Model_Observer extends Mage_Catalog_Model_Observer
{
    protected function _addCategoriesToMenu($categories, $parentCategoryNode, $menuBlock, $addTags = false)
    {
        $categoryModel = Mage::getModel('catalog/category');
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $categoryModel->setId($category->getId());
            if ($addTags) {
                $menuBlock->addModelTags($categoryModel);
            }

            $tree = $parentCategoryNode->getTree();
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                'url' => Mage::helper('catalog/category')->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category),
                'shortname' =>  $category->getData('shortname'),
                'description' => $category->getData('description'),
                'featuredproduct' => $category->getData('featuredproduct'),
            );
            $categoryNode = new Varien_Data_Tree_Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            $flatHelper = Mage::helper('catalog/category_flat');
            if ($flatHelper->isEnabled() && $flatHelper->isBuilt(true)) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode, $menuBlock, $addTags);
        }
    }

    /**
     * Before delete product, check product is used as a featured product.
     * If product is used as a featured product for one of the category then don't allow to delete the product
     * @param $observer
     * @throws Mage_Core_Exception
     */
    public function productDelete($observer)
    {
        $event   = $observer->getEvent();
        $product = $event->getProduct();
        $id      = $product->getId();
        $name    = $product->getName();

        /** @var $collections Mage_Catalog_Model_Resource_Category_Collection */
        $collections = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect(array('name'))
            ->addAttributeToFilter('featuredproduct',array('eq' => $id))
            ->load();
        if ($collections->count()) {
            $categoryName = '';
            foreach ($collections as $category) {
                /** @var $category Mage_Catalog_Model_Category */
                $categoryName = $category->getName();
            }
            throw Mage::exception('Mage_Core',
                $name . Mage::helper('eav')->__(' product is used as a featured product for Catagory ') . $categoryName
            );
        }
        return;
    }
}