<?php
/**
 * Description of Bilna_Paymethod_VtdirectController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_VtdirectController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'vtdirect';
    
    public function processingAction() {
        $session = Mage::helper('paymethod/vtdirect')->getCheckout();
        
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        
        if (!$lastQuoteId || (!$lastOrderId && empty ($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }
        
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Processing Page'));
        $this->renderLayout();
    }
    
    public function paymentAction() {
        $url = Mage::getStoreConfig('payment/vtdirect/charge_transaction_url');
        $data = $this->getRequest()->getPost();
        $charge = Mage::helper('paymethod/vtdirect')->postRequest($url, $data);
        //echo $charge;
        //$orderNo = $this->getRequest()->getPost('increment_id');
        echo $charge;
        exit;
    }
}
