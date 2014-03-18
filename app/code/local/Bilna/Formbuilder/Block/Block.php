<?php

/**
 * Cms block content block
 */
class Bilna_Formbuilder_Block_Block extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('formbuilder/form/default.phtml');
    }
    
    protected function _toHtml($status=false)
    {
    	$this->block = NULL;
        $this->blockId = $this->getBlockId();
        if ($this->blockId) {
			$this->block = Mage::getModel('bilna_formbuilder/formbuilder')->getCollection();
			$this->block->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id');
// 	                //->setStoreId(Mage::app()->getStore()->getId())
// 	                //->load($this->blockId);
// // 			$block->printLogQuery(true);die;
			$this->setCollection($this->block);
			
// // 	        if ($block->getStatus()) {
// 	        	/* @var $helper Mage_Cms_Helper_Data */
// // 	        	$helper = Mage::helper('bilna_formbuilder');
// 				//$processor = $helper->getBlockTemplateProcessor();
// // 	        }
        }
        
        $html = $this->renderView();
        return $html;
    }
}
