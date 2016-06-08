<?php
/**
 * API2 class for Afptc (admin)
 *
 * @category   Bilna
 * @package    Custom AW_Afptc 
 * @author     Development Team <development@bilna.com>
 */
class AW_Afptc_Model_Api2_Product_Rest_Admin_V1 extends AW_Afptc_Model_Api2_Product_Rest {
    protected function _retrieve()
    {
        $productId = (int) $this->getRequest()->getParam('product_id');
        $quoteId = (int) $this->getRequest()->getParam('quote_id');
        $qty = (int) $this->getRequest()->getParam('qty');
        $ruleId = (int) $this->getRequest()->getParam('rule_id');

        try{
                $quote = $this->_getQuote($quoteId);
                $rule = Mage::getModel('awafptc/rule')->load($ruleId);
                $ruleProductId = $rule->getProductId();

                if( $productId == $ruleProductId )
                {
                        $product = Mage::getModel('catalog/product')->load($ruleProductId);
                        if (!$product->getId())
                                throw Mage::throwException("Product with ID $productId doesn\'t exists");


                        $quote->addProduct($product->setData('aw_afptc_rule', $rule))->setQty($qty);

                        //Create the item for the quote
                        //bug fix price not calculated based on rule discount, its here!
                        //just save after add product
                        //link: http://findnerd.com/list/view/Add-product-to-cart-in-magento-programmatically/1144/
                        $quote->collectTotals()->save();
                }

        }catch(Exception $e){
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $quote;

        //return $this->_getLocation($quote);
    }
    
    protected function _create(array $filteredData) {
        $productId = (int) $filteredData['product_id'];
        $quoteId = (int) $filteredData['quote_id'];
        $qty = (int) $filteredData['qty'];
        $ruleId = (int) $filteredData['rule_id'];

    	try {
            $quote = $this->_getQuote($quoteId);
            $rule = Mage::getModel('awafptc/rule')->load($ruleId);
            $ruleProductId = $rule->getProductId();
            
            if ($productId == $ruleProductId) {
                $product = Mage::getModel('catalog/product')->load($ruleProductId);
                
                if (!$product->getId()) {
                    throw Mage::throwException("Product with ID $productId doesn\'t exists");
                }
                
                $quote->addProduct($product->setData('aw_afptc_rule', $rule))->setQty($qty);
                $quote->collectTotals()->save();
                
                $this->_getLocation($quote);
            }
            else {
                $this->_critical('Rule is not valid', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
            }
    	}
        catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
    	}
    }

    protected function _delete() {
        $productId = (int) $this->getRequest()->getParam('id');
        $quoteId = (int) $this->getRequest()->getParam('quote_id');
        
    }
}
