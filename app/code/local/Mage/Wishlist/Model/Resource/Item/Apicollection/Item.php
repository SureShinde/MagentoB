<?php
/**
 * Wishlist item collection for API only
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Model_Resource_Item_Apicollection_Item extends Mage_Wishlist_Model_Resource_Item_Collection
{
    /**
     * Add products to items and item options
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _assignProducts()
    {
        Varien_Profiler::start('WISHLIST:'.__METHOD__);
        $productIds = array();

        $isStoreAdmin = Mage::app()->getStore()->isAdmin();

        $storeIds = array();
        foreach ($this as $item) {
            $productIds[$item->getProductId()] = 1;
            if ($isStoreAdmin && !in_array($item->getStoreId(), $storeIds)) {
                $storeIds[] = $item->getStoreId();
            }
        }
        if (!$isStoreAdmin) {
            $storeIds = $this->_storeIds;
        }

        $this->_productIds = array_merge($this->_productIds, array_keys($productIds));
        $attributes = Mage::getSingleton('wishlist/config')->getProductAttributes();
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        foreach ($storeIds as $id) {
            $productCollection->addWebsiteFilter(Mage::app()->getStore($id)->getWebsiteId());
        }

        if ($this->_productVisible) {
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($productCollection);
        }

        $productCollection->addPriceData($this->_customerGroupId, $this->_websiteId)
            ->addTaxPercents()
            ->addIdFilter($this->_productIds)
            ->addAttributeToSelect($attributes)
            ->addOptionsToResult()
            ->addUrlRewrite();

        if ($this->_productSalable) {
            $productCollection = Mage::helper('adminhtml/sales')->applySalableProductTypesFilter($productCollection);
        }

        Mage::dispatchEvent('wishlist_item_collection_products_after_load', array(
            'product_collection' => $productCollection
        ));

        Varien_Profiler::stop('WISHLIST:'.__METHOD__);

        return $this;
    }
}
