<?php

class Amasty_Xlanding_Model_Page extends Mage_Core_Model_Abstract
{
    /**
     * Page's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const ON_SALE_YES = 2;
    const ON_SALE_NO = 1;
	
    const IS_NEW_YES = 2;
    const IS_NEW_NO = 1;
    
    const IS_INSTOCK_YES = 2;
    
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('amlanding/page');
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }
    
    public function getAttributesAsArray()
    {
    	$array = array();
    	$attributes = $this->getData('attributes');
    	if (!empty($attributes)) {
    		$array = unserialize($attributes);
    	}
    	return $array;
    }
    
    public function applyPageRules()
    {
    	$layer = Mage::getSingleton('catalog/layer');
    	/*
    	 * Category
    	 */
    	if ($this->getCategory()) {
    		$currentCategory = Mage::registry('current_category');
			$category = Mage::getModel('catalog/category')->load($this->getCategory());
			if ($category) { 
        		$layer->setCurrentCategory($category);
        	} 
			$collection = $layer->getProductCollection();
			//$collection->printLogQuery(true); exit;
			//default
    	}else{				
			$currentCategory = Mage::registry('current_category');
			$collection = Mage::getModel('catalog/product')
                     ->getCollection();
			$collection -> getSelect()
                     ->join(array ('ccpi' => 'catalog_category_product_index'), 'e.entity_id=ccpi.product_id AND ccpi.store_id = '.$collection->getStoreId().' AND ccpi.visibility IN ('.Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG.','.Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH.')')			
					 ->join(array ('cpip' => 'catalog_product_index_price'), 'e.entity_id=cpip.entity_id AND cpip.website_id = '.Mage::app()->getStore($store)->getWebsiteId().' AND cpip.customer_group_id = '.Mage::getSingleton('customer/session')->getCustomerGroupId().' ');					 
			
			//$collection->printLogQuery(true); exit;
			//blank
		}

    	/*
    	 * Apply Store Filter
    	 */
    	$collection->addStoreFilter();
    	
    	/*
    	 * Attributes
    	 */
    	$attributes = $this->getAttributesAsArray();
        if ($attributes){
        	foreach ($attributes as $code => $value) {
				$this->applyAttributeFilter($code, $collection, $value);
        	}
        }  
        
        /*
         * Stock Status
         */
        if ($stock = $this->getStockStatus()){
        	if ($stock == self::IS_INSTOCK_YES) {
				Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        	} 
        }
        
        /*
         * Is Product New
         */
        $newCriteriaDays = Mage::getStoreConfig('amlanding/advanced/new_criteria');
        if ($isNew = $this->getIsNew()) {
        	if ($isNew == self::IS_NEW_YES) {
        		 if ($newCriteriaDays) {
        			 $threshold = Mage::getStoreConfig('amlanding/advanced/new_threshold');
        			 $collection->getSelect()->where('datediff(now(), created_at) < ?', $threshold);
        		 } else {
					// $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
					// $collection
						// ->addAttributeToFilter('news_from_date', 
							// array(
							// 'or' => array(
			            		// 0 => array('date' => false, 'to' => $todayDate),
								// 1 => array('is' => new Zend_Db_Expr('null'))
							// )), 'left')
						// ->addAttributeToFilter('news_to_date', 
							// array('or'=> array(
		            			// 0 => array('date' => false, 'from' => $todayDate),
		            			// 1 => array('is' => new Zend_Db_Expr('null'))
							// )), 'left');
					$collection
					->addAttributeToFilter('news_from_date', array('lteq' =>date('Y-m-d')))
					->addAttributeToFilter('news_to_date', array('gteq' =>date('Y-m-d')));
        		}
        	}
        	
        	if ($isNew == self::IS_NEW_NO) {
        		if ($newCriteriaDays) {
					$threshold = Mage::getStoreConfig('amlanding/advanced/new_threshold');
        			$collection->getSelect()->where('datediff(now(), created_at) > ?', $threshold);
        		} else {
					//$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
// 					$collection
// 						->addAttributeToFilter('news_from_date', 
// 							array(
// 							'or' => array(
// 			            		0 => array('date' => false, 'from' => $todayDate),
// 								1 => array('is' => new Zend_Db_Expr('null'))
// 							)), 'left')
// 						->addAttributeToFilter('news_to_date', 
// 							array('or'=> array(
// 		            			0 => array('date' => false, 'to' => $todayDate),
// 		            			1 => array('is' => new Zend_Db_Expr('null'))
// 							)), 'left');
					/* AND condition
					$collection
						->addAttributeToFilter('news_from_date', array('gt' =>date('Y-m-d')))
						->addAttributeToFilter('news_to_date', array('lt' =>date('Y-m-d')));
					*/
					
					/* OR condition */
					$collection->addAttributeToFilter(array(
						array(
							'attribute' => 'news_from_date',
							'gt' => date('Y-m-d'),
							),
						array(
							'attribute' => 'news_to_date',
							'lt' => date('Y-m-d'),
							),
						array(
							'attribute' => 'news_from_date',
							'null' => true,
							),
						array(
							'attribute' => 'news_to_date',
							'null' => true,
							),
						));
        		}
        	}
        }
        
        /*
         * Is Product On Sale
         */
        if ($sale = $this->getIsSale()){
        	if ($sale == self::ON_SALE_YES) {
	            $collection
					//AND Condition
					->addAttributeToFilter('special_price', array('gt' => 0))
					->addAttributeToFilter('special_from_date', array('lteq' =>date('Y-m-d')))
					//->addAttributeToFilter('special_to_date', array('gteq' =>date('Y-m-d')))
					//OR Condition
					->addAttributeToFilter(array(
						array(
							'attribute' => 'special_to_date',
							'gteq' => date('Y-m-d'),
							),
						array(
							'attribute' => 'special_to_date',
							'null' => true,
							)
						));
        	} 
        	
        	if ($sale == self::ON_SALE_NO) {
	            // $collection
					// ->addAttributeToFilter('special_price', array('null'=>'special_price'), 'left');
					
				//masih mau di tes lg utk kondisi sale no
				// $collection->addAttributeToFilter(array(
						 // array('attribute' => 'special_price', 'null'=>true),
						 // "or" => array(
						 // array('attribute' => 'special_price', 'notnull'=>true),
							// ),							
						// ));	

				// $collection->addAttributeToFilter(array(
							// array('attribute' => 'special_price', 
								  // 'null' => true
									// ),
							// array('attribute' => 'special_price', 
								  // 'notnull' => true
									// ),
								// ));
				// $collection->addAttributeToFilter(array(
							// array('attribute' => 'special_from_date',
								  // 'lt' => date('Y-m-d')
									// ),
							// array('attribute' => 'special_to_date',
								  // 'gt' => date('Y-m-d')
									// ),
								// ));
				
				//AND (e.special_price > 0)
				//AND (e.special_from_date <= '2013-12-11')
				//AND ((e.special_to_date >= '2013-12-11') OR (e.special_to_date IS NULL))

				$collection->getSelect()
						   ->where("((`e`.`special_from_date` < '".date('Y-m-d')."') OR (`e`.`special_to_date` > '".date('Y-m-d')."')) OR ((`e`.`news_from_date` IS NULL) OR (`e`.`news_to_date` IS NULL))");								
        	}
        }  
		
		//$collection->printLogQuery(true); exit;
		
        $layer->prepareProductCollection($collection);
        
        /*
         * Restore Layer Category
         */
        if (isset($currentCategory)) {
      		$layer->setCurrentCategory($currentCategory);
        }
    }
    
    protected function applyAttributeFilter($code, $collection, $value)
    {
    	$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($code);
    	$filterable = $attribute->getIsFilterable();
    	
    	/*
    	 * Filterable, exists in index table
    	 */
    	if ($filterable) {
    		
	    	$alias = $code . $value . '_idx';
			$connection = $this->_getResource()->getReadConnection();
			$conditions = array(
				"{$alias}.entity_id = e.entity_id",
			    $connection->quoteInto("{$alias}.attribute_id = ?", $attribute->getAttributeId()),
			    $connection->quoteInto("{$alias}.store_id = ?",     $collection->getStoreId()),
			);
			
			$conditions[] = $connection->quoteInto("{$alias}.value  in (?)", $value);
	
			$collection->getSelect()->join(
				array($alias => Mage::getResourceModel('catalog/layer_filter_attribute')->getMainTable()),
				join(' AND ', $conditions),
			    array()
	        );
						
    	} else {
    		$collection->addFieldToFilter($code, $value); 
    	}
    }

  	public function massChangeStatus($ids, $status)
    {
        return $this->getResource()->massChangeStatus($ids, $status);
    }
}
