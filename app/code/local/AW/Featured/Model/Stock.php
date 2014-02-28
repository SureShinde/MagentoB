<?php
/**
 * Description of AW_Featured_Model_Stock
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class AW_Featured_Model_Stock extends Mage_CatalogInventory_Model_Stock {
    /**
     * Adds filtering for collection to return only in stock products
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection $collection
     * @return Mage_CatalogInventory_Model_Stock $this
     */
    public function addInStockFilterToCollection($collection) {
        //$this->getResource()->setInStockFilterToCollection($collection);
        Mage::getSingleton('awfeatured/resource_stock')->setInStockFilterToCollection($collection);
        
        return $this;
    }
}
