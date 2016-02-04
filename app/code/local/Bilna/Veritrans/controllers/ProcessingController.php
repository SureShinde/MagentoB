<?php
class Bilna_Veritrans_ProcessingController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'veritrans';

    public function redirectAction() {
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
    
    public function CancelAction() {
//        $session = Mage::helper('veritrans')->getCheckout();
//
//        if (!$session->getLastSuccessQuoteId()) {
//            $this->_redirect('checkout/cart');
//            return;
//        }
//
//        $lastQuoteId = $session->getLastQuoteId();
//        $lastOrderId = $session->getLastOrderId();
//        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
//        $lastRealOrderId = $session->getLastRealOrderId();
//
//        if (!$lastQuoteId || (!$lastOrderId && empty ($lastRecurringProfiles))) {
//            $this->_redirect('checkout/cart');
//            return;
//        }
//
//        if (!$this->_cancelStatusOrder($lastRealOrderId)) {
//            $this->_redirect('checkout/cart');
//            return;
//        }
//
//        $session->clear();
        $this->loadLayout();
        $this->renderLayout();
    }
	
    private function _cancelStatusOrder($orderId) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		
        if ($order->canCancel()) {
            if ($order->cancel()) {
                $comment = "Order was cancelled by customer";
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $comment, true)->save();
                
                return true;
            }
            
            return false;
        }
		
        return false;
    }
}
