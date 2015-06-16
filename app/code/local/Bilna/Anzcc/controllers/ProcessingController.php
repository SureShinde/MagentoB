<?php
class Bilna_Anzcc_ProcessingController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'anzcc';

    public function IndexAction() {
        $session = Mage::helper('veritrans')->getCheckout();
        
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
        $this->getLayout()->getBlock('head')->setTitle($this->__('Redirect Page'));
        $this->renderLayout();
    }
}
