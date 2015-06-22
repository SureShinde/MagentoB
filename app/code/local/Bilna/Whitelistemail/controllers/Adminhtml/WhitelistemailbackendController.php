<?php
/**
 * @author  Bilna Development Team <development@bilna.com>
 * 
 */

class Bilna_Whitelistemail_Adminhtml_WhitelistemailbackendController extends Mage_Adminhtml_Controller_action {
    public function indexAction() {
        $this->_title($this->__('Whitelist Email'));
        $this->loadLayout();
        $this->_setActiveMenu('bilna');
        $this->_addContent($this->getLayout()->createBlock('whitelistemail/adminhtml_whitelistemailbackend'));
        $this->renderLayout();
    }
    
    /**
     * Grid with Ajax Request
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('whitelistemail/adminhtml_whitelistemailbackend_grid')->toHtml()
        );
    }
    
    public function exportCsvAction() {
        $filename = sprintf("Installment_Report_%s.csv", date('Ymd', Mage::getModel('core/date')->timestamp(time())));
        $grid = $this->getLayout()->createBlock('Bilna_Whitelistemail_Block_Adminhtml_Whitelistemailbackend_Grid');
        $this->_prepareDownloadResponse($filename, $grid->getCsvFile()); 
    }
    
    public function massSendemailAction() {
        $customerIds = $this->getRequest()->getParam('customer_id'); // $this->getMassactionBlock()->setFormFieldName('customer_id'); from Bilna_Whitelistemail_Block_Adminhtml_Whitelistemailbackend_Grid
        
        if (!is_array($customerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('whitelistemail')->__('Please select Customers.'));
        }
        else {
            try {
                $whitelistemailModel = Mage::getModel('whitelistemail/processing');
                $prepareSendEmail = $whitelistemailModel->prepareSendEmailWhitelist($customerIds);
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('whitelistemail')->__('Total %d record(s) prepare to send.', $prepareSendEmail)
                );
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        
        $this->_redirect('*/*/index');
    }
}
