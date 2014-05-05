<?php

class Bilna_Staticarea_Block_Block extends Mage_Core_Block_Template {

	private $_block = null;

	protected function _beforeToHtml() {
        if(!$this->getTemplate()) {
            $this->setTemplate('bilna_staticarea/blocks.phtml');
        }
        return parent::_beforeToHtml();
    }

    public function getBlock()
    {
    	$store_id = Mage::app()->getStore()->getStoreId(); 

        if($this->_block === null) {
            if($this->getData('id')){
                $this->_block = Mage::getModel('staticarea/contents')->getCollection();
                $this->_block->addFieldToSelect("content", "content");
                $this->_block->addFieldToSelect("url", "url");
                $this->_block->addFieldToFilter("staticarea.block_id", array ('eq' => $this->getData('id'))); 
                //$this->_block->addFieldToFilter("staticarea.storeview", array ('like' => '%'.$store_id.'%'));
                $this->_block->addFieldToFilter("status", array ('eq' => 1));
                $this->_block->addFieldToFilter("DATE(NOW())", array ('gteq' => new Zend_Db_Expr('active_from')));
                $this->_block->addFieldToFilter("DATE(NOW())", array ('lteq' => new Zend_Db_Expr('active_to')));
                $this->_block->getSelect()
					->joinLeft(
						array( 'staticarea' => Mage::getSingleton('core/resource')->getTableName('staticarea/manage') ),
						"main_table.staticarea_id = staticarea.id",
						array( 
							'area_name' => 'staticarea.area_name',
                            'type' => 'staticarea.type'
						)
					);
                $this->_block->setOrder('`order`', 'ASC');
            }
            if(!$this->_block->getData())
                $this->_block = null;
        }
      
        return $this->_block;
    }

    public function getHtmlCode($block = null)
    {
        if(is_null($block))
            $block = $this->getBlock();
        $block->afterLoad();
        if($block) {
			$helper = Mage::helper('cms');
            $processor = $helper->getBlockTemplateProcessor();
            $html = $processor->filter($block->getContent());
            return $html;
        } else {
            return null;
        }
        return $block->toHtml();
    }
}