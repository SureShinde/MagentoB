<?php
class Bilna_Formbuilder_Block_Index extends Mage_Core_Block_Template
{
	public function _prepareLayout()
  {
		return parent::_prepareLayout();
  }
    
	public function getPromo()     
	{ 
		if (!$this->hasData('formbuilder')) {
		    $this->setData('formbuilder', Mage::registry('formbuilder'));
		}
		return $this->getData('formbuilder');        
	}
}
