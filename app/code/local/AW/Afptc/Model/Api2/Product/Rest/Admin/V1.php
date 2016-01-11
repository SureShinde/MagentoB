<?php

/**
 * API2 class for Afptc (admin)
 *
 * @category   Bilna
 * @package    Custom AW_Afptc 
 * @author     Development Team <development@bilna.com>
 */
class AW_Afptc_Model_Api2_Product_Rest_Admin_V1 extends AW_Afptc_Model_Api2_Product_Rest
{
	/**
     * Add Free Product to Cart 
     *
     * @param int|string $store
     * @return int
     */
    protected function _retrieve()
    {
        $productId = (int) $this->getRequest()->getParam('product_id');
    	$quoteId = (int) $this->getRequest()->getParam('quote_id');
    	$qty = (int) $this->getRequest()->getParam('qty');
    	$ruleId = (int) $this->getRequest()->getParam('rule_id');

    	try{
    		$quote = $this->_getQuote($quoteId);
    		$rule = Mage::getModel('awafptc/rule')->load($ruleId);

    		if( $productId == $rule->getProductId() )
    		{
    			$product = Mage::getModel('catalog/product')->load($rule->getProductId());
    			if (!$product->getId())
    				throw Mage::throwException("Product with ID $productId doesn\'t exists");

    			$quote->addProduct($product->setData('aw_afptc_rule', $rule))->setQty($qty);
    		}

    	}catch(Exception $e){
    		$this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
    	}
        
        return $quote;

    	//return $this->_getLocation($quote);
    }
    /*
    protected function _create(array $data)
    {
    	$productId = (int) $data['product_id'];
    	$quoteId = (int) $data['quote_id'];
    	$qty = (int) ( isset($data['qty']) ? $data['qty'] : 1);
    	$ruleId = (int) $data['rule_id'];

    	try{
    		$quote = $this->_getQuote($quoteId);
    		$rule = Mage::getModel('awafptc/rule')->load($ruleId);

    		if( $productId == $rule->getProductId() )
    		{
    			$product = Mage::getModel('catalog/product')->load($rule->getProductId());
    			if (!$product->getId())
    				throw Mage::throwException("Product with ID $productId doesn\'t exists");

    			$quote->addProduct($product->setData('aw_afptc_rule', $rule))->setQty($qty);
    		}

    	}catch(Exception $e){
    		$this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
    	}

    	return $this->_getLocation($quote);
    }
    */
}