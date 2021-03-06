<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Alert_Adminhtml_PriceController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction() 
	{
	    $this->loadLayout(); 
        $this->_setActiveMenu('report/amalert');
        if (!Mage::helper('ambase')->isVersionLessThan(1,4)){
            $this
                ->_title($this->__('Reports'))
                ->_title($this->__('Alerts'))
                ->_title($this->__('Price Alerts')); 
        }
        $this->_addBreadcrumb($this->__('Alerts'), $this->__('Price Alerts')); 
        $this->_addContent($this->getLayout()->createBlock('amalert/adminhtml_price')); 	    
 	    $this->renderLayout();
	}
}