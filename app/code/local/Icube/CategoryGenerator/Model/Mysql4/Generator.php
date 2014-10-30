<?php
class Icube_CategoryGenerator_Model_Mysql4_Generator extends Mage_Rule_Model_Mysql4_Rule {
    protected function _construct() {
        $this->_init('categorygenerator/generator', 'id');
    }
    
    public function updateGeneratorProductData(Icube_CategoryGenerator_Model_Generator $rule) {
    	if(!is_null(Mage::getSingleton('core/session')->getCategoryGeneratorId()) && ($rule->getId() <> Mage::getSingleton('core/session')->getCategoryGeneratorId())){
//     		Mage::getSingleton('core/session')->unsCategoryGeneratorId();
    		return $this;
    	}else{
//     		Mage::getSingleton('core/session')->unsCategoryGeneratorId();
    	}

        if (!$rule->getIsActive()) {
            Mage::helper('categorygenerator')->logprogress("ruleId: " . $ruleId . " => Inactive");
            return $this;
        }
    	
    	$ruleId = $rule->getId();
        echo "start rule with id: ".$ruleId."\n";
    	
        $now = strtotime(date("Y-m-d", Mage::getModel('core/date')->timestamp(time())));
        Mage::helper('categorygenerator')->logprogress("ruleId: " . $ruleId . " => start");
        
        $categoryIds = @unserialize($rule->getCategoryData());
        $categoryIdsArr = explode(',', $categoryIds['categories']);
        Mage::helper('categorygenerator')->logprogress("categoryIdsArr: " . json_encode($categoryIdsArr));
        
        foreach ($categoryIdsArr as $categoryId) {
            try {
        		echo "start category with id: ".$categoryId."\n";
                Mage::helper('categorygenerator')->logprogress("categoryId: " . $categoryId . " => assignedProducts");
                $assignedProducts = Mage::getSingleton('catalog/category_api')->assignedProducts($categoryId);

                /**
                 * remove product from category
                 */
                foreach ($assignedProducts as $assignedProduct) {
        			echo "remove product id: ".$assignedProduct['product_id']."\n";
                    Mage::helper('categorygenerator')->logprogress("productId: " . $assignedProduct['product_id'] . " => removeProduct");
                    Mage::getSingleton('catalog/category_api')->removeProduct($categoryId, $assignedProduct['product_id']);
                }
		
		        Varien_Profiler::start('__MATCH_PRODUCTS__');
		        $productIds = $rule->getMatchingProductIds();
		        Varien_Profiler::stop('__MATCH_PRODUCTS__');
		        
		        if (empty ($productIds)) {
		            Mage::helper('categorygenerator')->logprogress('There is no product matching with the conditions');
		            Mage::getSingleton('adminhtml/session')->addWarning('There is no product matching with the conditions');
		            //throw new Exception('There is no product matching with the conditions');
		            
		            return $this;
		        }

                /**
                 * assign product to category
                 */
                foreach ($productIds as $productId) {
                    $product = Mage::getModel('catalog/product');
                    $product->unsetData()->load($productId);
                    
                    $newsFromDate = $product->getNewsFromDate();
                    $newsToDate = $product->getNewsToDate();
                    $specialFromDate = $product->getSpecialFromDate();
                    $specialToDate = $product->getSpecialToDate();

                    //check if is_new
                    if (($rule->getIsNew() == 1) && 
                        ((is_null($newsFromDate) && is_null($newsToDate)) || 
                        (!is_null($newsFromDate) && strtotime($newsFromDate) > $now) || 
                        (!is_null($newsToDate) && strtotime($newsToDate) < $now))) {
                        continue;
                    }
                    elseif (($rule->getIsNew() == 2) && 
                        (!(is_null($newsFromDate) && is_null($newsToDate)) && 
                        (((is_null($newsFromDate) || strtotime($newsFromDate) <= $now) && 
                        		strtotime($newsToDate) >= $now) ||
                        ((!is_null($newsFromDate) && strtotime($newsFromDate) <= $now) && (is_null($newsToDate) || 
                        		strtotime($newsToDate) >= $now))))) {
                        continue;
                    }

                    //check if is_onsale
                    if (($rule->getIsOnsale() == 1) && 
                        ((is_null($specialFromDate) && is_null($specialToDate)) || 
                        (!is_null($specialFromDate) && strtotime($specialFromDate) > $now) || 
                        (!is_null($specialToDate) && strtotime($specialToDate) < $now))) {
                        continue;
                    }
                    elseif (($rule->getIsOnsale() == 2) && 
                        (!(is_null($specialFromDate) && is_null($specialToDate)) && 
                        (((is_null($specialFromDate) || strtotime($specialFromDate) <= $now) && 
                        		strtotime($specialToDate) >= $now) ||
                        ((!is_null($specialFromDate) && strtotime($specialFromDate) <= $now) && (is_null($specialToDate) || 
                        		strtotime($specialToDate) >= $now))))) {
                        continue;
                    }

                    echo "assign product id: ".$productId."\n";
                    Mage::helper('categorygenerator')->logprogress("productId: " . $productId . " => assignProduct");
                    Mage::getSingleton('catalog/category_api')->assignProduct($categoryId, $productId);
                }
            }
            catch (Exception $e) {
                $message = "categoryId: {$categoryId} {$e->getMessage()}";
                Mage::helper('categorygenerator')->logprogress($message);
                //Mage::getSingleton('adminhtml/session')->addError(Mage::helper('categorygenerator')->__($message));
                //$this->rollback();
                continue;
            }
        }

        Mage::helper('categorygenerator')->logprogress("ruleId: " . $ruleId . " => stop");
        
        return $this;
    }
}
