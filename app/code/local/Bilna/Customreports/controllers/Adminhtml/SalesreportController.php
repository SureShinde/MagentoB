<?php

class Bilna_Customreports_Adminhtml_SalesreportController extends Mage_Adminhtml_Controller_Report_Abstract
{
	public function indexAction() 
	{
    	$this	->_title($this->__('Custom Reports'))->_title($this->__('Sales Report'));

	    $this	->_initAction()
			    ->_setActiveMenu('bilna/customreports/salesreport')
			    ->_addBreadcrumb(Mage::helper('customreports')->__('Custom Reports'), Mage::helper('customreports')->__('Sales Report'));
		    
	    $gridBlock = $this->getLayout()->getBlock('Bilna_Customreports_Block_Adminhtml_Salesreport');
	    $filterFormBlock = $this->getLayout()->getBlock('Bilna_Customreports_Block_Adminhtml_Salesreportfilter');

	    $this->_initReportAction(array(
	    		$gridBlock,
	    		$filterFormBlock
	    ));
	    
	    $this->renderLayout();
	}

	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'customreports_salesreport.csv';
		$grid       = $this->getLayout()->createBlock('customreports/adminhtml_salesreport_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	} 
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'customreports_salesreport.xml';
		$grid       = $this->getLayout()->createBlock('customreports/adminhtml_salesreport_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
}
