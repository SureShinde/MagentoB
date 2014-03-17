<?php

/**
 * Cms block content block
 */
class Bilna_Formbuilder_Block_Block extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }
    
    protected function _toHtml($status=false)
    {
    	$block = NULL;
        $blockId = $this->getBlockId();
        $html = '';
        if ($blockId) {
			$block = Mage::getModel('bilna_formbuilder/formbuilder')->getCollection();
			$block->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id');
	                //->setStoreId(Mage::app()->getStore()->getId())
	                //->load($blockId);
// 			$block->printLogQuery(true);die;
			$this->setCollection($block);
			
// 	        if ($block->getStatus()) {
	        	/* @var $helper Mage_Cms_Helper_Data */
// 	        	$helper = Mage::helper('bilna_formbuilder');
				//$processor = $helper->getBlockTemplateProcessor();
// 	        }
        }
        
        $this->setTemplate('formbuilder/form/default.phtml');
        return $this->_toHtml(true);
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }
}