<?php
class Bilna_Tanyadokter_Block_Index extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getPromo()     
     { 
        if (!$this->hasData('tanyadokter')) {
            $this->setData('tanyadokter', Mage::registry('tanyadokter'));
        }
        return $this->getData('tanyadokter');
        
    }
}