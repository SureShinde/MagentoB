<?php

class AW_Affiliate_Block_Campaign_Product_List extends Mage_Catalog_Block_Product_List
{
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     **/
    public function getNewProductCollection($limit)
    {
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
             /*->addAttributeToFilter('news_to_date', array('or'=> array(
                0 => array('date' => true, 'from' => $todayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
             ), 'left')*/
            ->addAttributeToSort('news_from_date', 'desc');
        $collection->getSelect()->limit( $limit ); 
//$collection->printLogQuery(true); 
        $this->setProductCollection($collection);

        return $collection;
   }

   public function getBestProductCollection($campaign_id, $limit)
   {
        //$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getResourceModel('catalog/product_collection');
        //$collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());

        $collection->getSelect()
            ->joinInner(
            array( 'prod_index' => Mage::getSingleton('core/resource')->getTableName('awaffiliate/products') ),
            "prod_index.product_id = e.entity_id AND prod_index.campaign_id='".$campaign_id."'"
        );

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter();
/*            ->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
            ->addAttributeToSort('news_from_date', 'desc');*/
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));    
        $collection->getSelect()->limit( $limit ); 

        $this->setProductCollection($collection);

        return $collection;
   }

   public function getCategoryProductCollection($category_id='2', $limit)
   {
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getResourceModel('catalog/product_collection');
        //$collection->setVisibility(array_push(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), array('category_id' => $category_id))); 
        $collection->getSelect()
            ->joinInner(
            array( 'cat_index' => Mage::getSingleton('core/resource')->getTableName('catalog/category_product_index') ),
            "cat_index.product_id = e.entity_id AND cat_index.category_id= '".$category_id."' AND cat_index.visibility IN (".implode(",", Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()).") AND cat_index.store_id='1'",
            array( 
                'category_id'        => 'cat_index.category_id'
            )
        );

        $collection = $this->_addProductAttributesAndPrices($collection);
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
        $collection->getSelect()->limit( $limit ); 

        $this->setProductCollection($collection);
        
        return $collection;
   }
}

?>