<?php
class Bilna_Customreports_Adminhtml_InstallmentreportController extends Mage_Adminhtml_Controller_action {
    public function indexAction() {
        $this->_title($this->__('Installment Report'));
        $this->loadLayout();
        $this->_setActiveMenu('customreports/installmentreport');
        $this->_addContent($this->getLayout()->createBlock('customreports/adminhtml_installmentreport'));
        $this->renderLayout();
    }
    
    /**
     * Grid with Ajax Request
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('customreports/adminhtml_installmentreport_grid')->toHtml()
        );
    }
    
    public function exportCsvAction() {
        $filename = sprintf("Installment_Report_%s.csv", date('Ymd', Mage::getModel('core/date')->timestamp(time())));
        $grid = $this->getLayout()->createBlock('Bilna_Customreports_Block_Adminhtml_Installmentreport_Grid');
        $this->_prepareDownloadResponse($filename, $grid->getCsvFile()); 
    }
}
