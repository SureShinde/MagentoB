<?php
class Bilna_Formbuilder_Adminhtml_FormbuilderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Bilna'))->_title($this->__('Bilna Form Builder Manager'));
        $this->loadLayout();
        $this->_setActiveMenu('bilna/bilna');
        $this->_addContent($this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder'));
        $this->renderLayout();
		// $this->_initAction()
			 // ->renderLayout();
    }
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bilna_formbuilder/adminhtml_formbuilder_grid')->toHtml()
        );
    }
	/** 
     * Export order grid to CSV format 
     */ 
    public function exportCsvAction() 
    { 
        $fileName   = 'bilna_formbuilder.csv'; 
        $grid       = $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Grid'); 
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile()); 
    } 
}