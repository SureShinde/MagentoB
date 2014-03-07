<?php
class Bilna_Staticarea_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {	
       /*$this->loadLayout();
	   $this->_title($this->__("Static Area Management"));
	   $this->renderLayout();*/
	   return $this->_redirect('*/*/edit');
    }

    protected function newAction()
    {
        //Mage::helper('staticarea')->setFormData(array());
        return $this->_redirect('*/*/edit');
    }
    
    protected function listAction()
    {
        $this->_initAction()->_setTitle($this->__('List Sliders'));
        $this->renderLayout();
    }
    
    protected function editAction()
    {     
    	$this->loadLayout();
    	$this->_setActiveMenu("staticarea/index");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('staticarea/adminhtml_area_edit'))
            ->_addLeft($this->getLayout()->createBlock('staticarea/adminhtml_area_edit_tabs'));
        $this->renderLayout();
    }
}