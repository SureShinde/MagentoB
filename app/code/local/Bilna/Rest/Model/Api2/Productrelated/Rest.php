<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productrelated_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Productrelated_Rest extends Bilna_Rest_Model_Api2_Productrelated {
    protected $_blocks = null;
    protected $_blockPosition = null;
    protected $_blockType = null;
    
//    <option value="4">Before content</option>
//    <option value="2">Instead native related block</option>
//    <option value="3">Under native related block</option>
//    <option value="1">Inside product page</option>
//    <option selected="selected" value="0">Custom</option>
    
    /**
     * 
     * @return int
     */
    protected function _getBlockPosition() {
        $blockName = $this->getRequest()->getQuery('name');
        
        $collection = Mage::getModel('awautorelated/blocks')->getCollection();
        $collection->addFieldToFilter('name', $blockName);
        $collection->printLogQuery(true);exit;
        
        if ($this->_blockPosition === null) {
            $this->_blockPosition = $this->getResponse()->getParam('safda');
        }
        
        return $this->_blockPosition;
    }

    protected function _getBlocks() {
        $helper = Mage::helper('awautorelated');
        
        if ($this->_blocks === null) {
            if ($this->getBlockPosition() != AW_Autorelated_Model_Source_Position::CUSTOM) {
                $collection = Mage::getModel('awautorelated/blocks')->getCollection();
                $collection->addStoreFilter()
                    ->addPositionFilter($this->getBlockPosition())
                    ->addStatusFilter()
                    ->addCustomerGroupFilter($helper->getCurrentUserGroup())
                    ->addDateFilter()
                    ->setPriorityOrder();
                if ($this->_blockType) {
                    $collection->addTypeFilter($this->_blockType);
                }
                $this->_blocks = $collection;
            } else if ($this->getData('block_id')) {
                $collection = Mage::getModel('awautorelated/blocks')->getCollection();
                $collection->addStoreFilter()
                    ->addStatusFilter()
                    ->addCustomerGroupFilter($helper->getCurrentUserGroup())
                    ->addDateFilter()
                    ->addIdFilter($this->getData('block_id'));
                $this->_blocks = $collection;
            }
        }
        return $this->_blocks;
    }
}
