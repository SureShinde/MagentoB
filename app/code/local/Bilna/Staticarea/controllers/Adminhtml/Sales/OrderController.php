<?php
/**
 * Description of Bilna_Wrappinggiftevent_Adminhtml_Sales_OrderController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Wrappinggiftevent_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action {
    public function saveWrappinggiftAction() {
        $model = Mage::getModel('wrappinggiftevent/custom_quote');
        $request = $this->getRequest();
        $session = Mage::getSingleton('adminhtml/session_quote');
        $quote = $session->getQuote();
        
        if (!$session || !$session->getQuote()) {
            return;
        }
        
        if (is_null($request->getParam('use_wrappinggift', null)) || is_null($request->getParam('use_wrappinggift', null))) {
            /**
             * delete from admin session
             */
            $result = array (
                'use_wrappinggift' => null,
                'wrappinggift_amount' => null
            );
            $session->deleteData($result);
            
            /**
             * delete from quote
             */
            $session->setWrappingType(null);
            $session->setWrappingPrice(null);
            $model = Mage::getModel('wrappinggiftevent/custom_quote');
            $model->deteleByQuote($session->getId());
        }
        else {
            /**
             * save to admin session
             */
            $result = array (
                'use_wrappinggift' => $request->getParam('use_wrappinggift'),
                'wrappinggift_amount' => $request->getParam('wrappinggift_amount')
            );
            $session->addData($result);
            
            /**
             * save to quote
             */
            $arr = explode('_', $result['wrappinggift_amount']);
            $wrappingType = $arr[0];
            $wrappingPrice = $arr[1];
            $session->setWrappingType($wrappingType);
            $session->setWrappingPrice($wrappingPrice);
            
            $this->saveCustomQuote($quote, $wrappingType, $wrappingPrice);
        }
        
        return;
    }
    
    private function saveCustomQuote($quote, $wrappingType, $wrappingPrice) {
        $storeId = $quote->getStoreId();
        $quoteId = $quote->getId();               
        $model = Mage::getModel('wrappinggiftevent/custom_quote');
        $model->deteleByQuote($quote->getId());
        $model->setQuoteId($quote->getId());
        $model->setStoreId($quote->getStoreId());
        $model->setWrappingType($wrappingType);
        $model->setWrappingPrice($wrappingPrice);
        $model->setCreatedAt(date('Y-m-d H:i:s'));
        $model->save();
        
        return;
    }
}
