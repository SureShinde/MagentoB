<?php
class Bilna_Anzcc_Adminhtml_AnzccbackendController extends Mage_Adminhtml_Controller_action {
    public function indexAction() {
        $this->_title($this->__('Installment Report'));
        $this->loadLayout();
        $this->_setActiveMenu('anzcc/azccbackend');
        $this->_addContent($this->getLayout()->createBlock('anzcc/adminhtml_anzccbackend'));
        $this->renderLayout();
    }
    
    /**
     * Grid with Ajax Request
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('anzcc/adminhtml_anzccbackend_grid')->toHtml()
        );
    }
    
    public function exportCsvAction() {
        $filename = sprintf("Installment_Report_%s.csv", date('Ymd', Mage::getModel('core/date')->timestamp(time())));
        $grid = $this->getLayout()->createBlock('Bilna_Anzcc_Block_Adminhtml_Anzccbackend_Grid');
        $this->_prepareDownloadResponse($filename, $grid->getCsvFile()); 
    }
}
