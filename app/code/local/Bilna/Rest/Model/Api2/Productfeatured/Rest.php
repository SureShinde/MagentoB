<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productfeatured_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Productfeatured_Rest extends Bilna_Rest_Model_Api2_Productfeatured {
    protected $_blockId = null;
    protected $_block = null;

    protected function _getParams() {
        $this->_blockId = $this->getRequest()->getParam('block_id');
    }
    
    protected function _getBlock() {
        if (is_null($this->_block)) {
            $this->_block = Mage::getModel('awfeatured/blocks')->setStoreId($this->_getStore()->getId())->loadByBlockId($this->_blockId);
            
            if (!$this->_block->getId()) {
                $this->_error('Block not found', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
        }
        
        return $this->_block;
    }
    
    protected function _parseBlock($blockData) {
        if (isset ($blockData['automation_data']) && isset ($blockData['automation_data']['products'])) {
            $limit = isset ($blockData['type_data']['productsinrow']) ? $blockData['type_data']['productsinrow'] : 10;
            $products = explode(",", $blockData['automation_data']['products']);
            $productsArr = array ();
            $x = 0;
            
            foreach ($products as $product) {
                if ($x == $limit) {
                    break;
                }
                
                $productsArr[] = $product;
                $x++;
            }
            
            $blockData['automation_data']['products'] = $productsArr;
        }
        
        return $blockData;
    }
}
