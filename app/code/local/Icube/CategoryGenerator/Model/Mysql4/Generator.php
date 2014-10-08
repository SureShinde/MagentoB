<?php

class Icube_CategoryGenerator_Model_Mysql4_Generator extends Mage_Rule_Model_Mysql4_Rule
{

    protected function _construct()
    {
        $this->_init('categorygenerator/generator', 'id');
    }
    
    public function updateGeneratorProductData(Icube_CategoryGenerator_Model_Generator $rule)
    {
    	
    	$ruleId = $rule->getId();

        if (!$rule->getIsActive()) {
            return $this;
        }

        Varien_Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingProductIds();
        Varien_Profiler::stop('__MATCH_PRODUCTS__');
        
        if (empty($productIds)) {
        	Mage::getSingleton('adminhtml/session')->addWarning('There is no product matching with the conditions');
            return $this;
        }
        
        $categoryIds = @unserialize($rule->getCategoryData());

        try {
            foreach($categoryIds as $categoryId)
		    {
		    	  $assignedProducts = Mage::getSingleton('catalog/category_api')->assignedProducts($categoryId); 
		    	  foreach ($assignedProducts as $assignedProduct) {
		        	  Mage::getSingleton('catalog/category_api')->removeProduct($categoryId,$assignedProduct['product_id']);
		    	  }
		    	  
		          foreach ($productIds as $productId) {
		          	Mage::getSingleton('catalog/category_api')->assignProduct($categoryId,$productId);
		          }
		    }
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }


        return $this;	
    }
}