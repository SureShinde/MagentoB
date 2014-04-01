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
            return $this;
        }
        
        $categoryIds = @unserialize($rule->getCategoryData());

        try {
            foreach ($productIds as $productId) {
                        $product = Mage::getModel('catalog/product')->load($productId);
                        $product->setCategoryIds($categoryIds);
                        $product->save();
                    }
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }


        return $this;	
    }
}