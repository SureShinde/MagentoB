<?php
class Bilna_Veritrans_IndexController extends Mage_Core_Controller_Front_Action {
    private $_kode = "veritrans";

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
            $this->_writeLog(sprintf("Order #%s can canceled.", $orderId));

            if ($order->cancel()) {
                $comment = "Order was cancelled by customer";
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $comment, true)->save();
                $this->_writeLog(sprintf("Order #%s was canceled.", $orderId));
				
                return true;
            }

            $this->_writeLog(sprintf("Order #%s failed canceled.", $orderId));
            
            return false;
        }

        $this->_writeLog(sprintf("Order #%s can't canceled.", $orderId));
		
        return false;
    }
	
    private function _writeLog($message) {
        //echo "tulis log dimari";
        $today = date('Ymd');
        //Mage::log('tulis log dimari', null, 'veritrans.1234567890.log');
        Mage::log($message, null, sprintf("%s.%s.log", $this->_kode, $today));
    }
}
