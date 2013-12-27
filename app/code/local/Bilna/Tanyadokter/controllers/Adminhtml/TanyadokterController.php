<?php
class Bilna_Tanyadokter_Adminhtml_TanyadokterController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Bilna'))->_title($this->__('Bilna Tanya Dokter Manager'));
        $this->loadLayout();
        $this->_setActiveMenu('bilna/bilna');
        $this->_addContent($this->getLayout()->createBlock('bilna_tanyadokter/adminhtml_tanyadokter'));
        $this->renderLayout();
		// $this->_initAction()
			 // ->renderLayout();
    }
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bilna_tanyadokter/adminhtml_tanyadokter_grid')->toHtml()
        );
    }
	/** 
     * Export order grid to CSV format 
     */ 
    public function exportCsvAction() 
    { 
        $fileName   = 'bilna_tanyadokter.csv'; 
        $grid       = $this->getLayout()->createBlock('Bilna_Tanyadokter_Block_Adminhtml_Tanyadokter_Grid'); 
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile()); 
    } 
}