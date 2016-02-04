<?php

class Bilna_Customreports_Adminhtml_CouponsreportController extends Mage_Adminhtml_Controller_Report_Abstract
{

	public function indexAction()
	{
    	$this	->_title($this->__('Coupons Report'))->_title($this->__('Manager Report'));

	    $this	->_initAction()
			    ->_setActiveMenu('bilna/customreports/couponsreport')
			    ->_addBreadcrumb(Mage::helper('customreports')->__('Coupons Report'), Mage::helper('customreports')->__('Manager Report'));

	    $gridBlock = $this->getLayout()->getBlock('Bilna_Customreports_Block_Adminhtml_Couponsreport');
	    $filterFormBlock = $this->getLayout()->getBlock('Bilna_Customreports_Block_Adminhtml_Couponsreportfilter');

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
		$fileName   = 'report.csv';
		$grid       = $this->getLayout()->createBlock('customreports/adminhtml_couponsreport_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}

	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'report.xml';
		$grid       = $this->getLayout()->createBlock('customreports/adminhtml_couponsreport_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

}
