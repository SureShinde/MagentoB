<?php
class Bilna_Catalog_Model_Resource_Url extends Mage_Catalog_Model_Resource_Url
{
	public function getCategoryParentCompleteUrl(Varien_Object $category)
    {
        $store = Mage::app()->getStore($category->getStoreId());

        if ($category->getId() == $store->getRootCategoryId()) {
            return '';
        } elseif ($category->getParentId() == 1 || $category->getParentId() == $store->getRootCategoryId()) {
            return '';
        }

        $parentCategory = $this->getCategory($category->getParentId(), $store->getId());
        return $parentCategory->getCompleteUrl() . '/';
    }
}