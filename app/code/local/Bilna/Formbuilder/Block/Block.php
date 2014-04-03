<?php

//Cms block content block
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
    	$this->inputs = NULL;
      $this->blockId = $this->getBlockId();

      if ($this->blockId) {
				$this->block = Mage::getModel('bilna_formbuilder/form')->getCollection();
				$this->block->getSelect();
				$this->block->addFieldToFilter('main_table.id', $this->blockId);
				$this->block = $this->block->getFirstItem();
			
				$this->inputs = Mage::getModel('bilna_formbuilder/form')->getCollection();
				$this->inputs->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id');
				$this->inputs->addFieldToFilter('main_table.id', $this->blockId);
      }

        $html = $this->renderView();
        return $html;
    }
}
