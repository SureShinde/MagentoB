<?php
/**
 *
 * @category    Bilna
 * @package     Bilna_Wrappinggiftevent
 * @copyright   Copyright (c) 2014 PT Bilna. (http://www.bilna.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout 
 *
 * @category   Bilna
 * @category   Bilna
 * @package    Bilna_Wrappinggiftevent
 * @author     Bilna Development Team <development@bilna.com>
 */

class Bilna_Wrappinggiftevent_Model_Observer {
    protected $tdate;
    
    public function __construct() {
        $this->tdate = date('Y-m-d H:i:s');
    }

    public function saveQuoteAfter($evt)
    {
        $quote = $evt->getQuote();
        if($quote->getWrappingType())
        {
            $type = $quote->getWrappingType();
            $price = $quote->getWrappingPrice();
            if( !empty($type) && !empty($price) )
            {
                
                $storeId = $quote->getStoreId();
                $quoteId = $quote->getId();               
                $model = Mage::getModel('wrappinggiftevent/custom_quote');
                $model->deteleByQuote($quote->getId());
                $model->setQuoteId($quote->getId());
                $model->setStoreId($quote->getStoreId());
                $model->setWrappingType($type);
                $model->setWrappingPrice($price);
                $model->setCreatedAt($this->tdate);
                $model->save();
            }
        }
    }
    
    public function loadQuoteAfter($evt)
    {
        $quote = $evt->getQuote();
        $model = Mage::getModel('wrappinggiftevent/custom_quote');    
        $data  = $model->getByQuote($quote->getId());
        foreach($data as $key => $value){
            $quote->setData($key,$value);
        }
    }
    
    public function saveOrderAfter($evt)
    {
        $order = $evt->getOrder();
        $quote = $evt->getQuote();

        $type = $quote->getWrappingType();
        $price =  $quote->getWrappingPrice();       
        
        if( $type && $price )
        {
            $type  = $quote->getWrappingType();
            $price = $quote->getWrappingPrice();
            
            if( !empty($type) && !empty($price) )
            {
                $model = Mage::getModel('wrappinggiftevent/custom_order');
                $model->deleteByOrder($order->getId());
                $model->setOrderId($order->getId());
                $model->setStoreId($order->getStoreId());
                $model->setWrappingType($type);
                $model->setWrappingPrice($price);
                $model->setCreatedAt($this->tdate);
                $model->save();
            }
        }
    }
    
    public function loadOrderAfter($evt)
    {
        $order = $evt->getOrder();
        $model = Mage::getModel('wrappinggiftevent/custom_order');
        $data = $model->getByOrder($order->getId());
        if( !empty($data) ) {
            foreach($data as $key => $value){
                $order->setData($key,$value);
            }
        }
    }

    public function paymentAddWrapping(Varien_Event_Observer $observer)
    {
        $input = $observer->getEvent()->getInput();
        $session = Mage::getSingleton('checkout/session');

        $session->setData('use_points', $input->getData('use_points'));
        $session->setData('points_amount', $input->getData('points_amount'));

        if ($session->getData('use_points') && !$input->getData('method')) {
            $input->setMethod('free');
        }
        return $this;
    }

    public function quoteDistroy($evt)
    {
        $quote = $evt->getQuote();
        $quote->setWrappingType(null);
        $quote->setWrappingPrice(null);
    }

    public function paymentAddWrap(Varien_Event_Observer $observer)
    {
        $quote = $observer->getQuote();
        $post = Mage::app()->getFrontController()->getRequest()->getPost();

        if( $post['wrapping_for_gift'] == 'on' && isset($post['wrapping']['gift']))
        {
            $arr = explode("_", $post['wrapping']['gift']);
            $type = $arr[0];
            $price = $arr[1];            
            $quote->setWrappingType($type);
            $quote->setWrappingPrice($price);
            $quote->setCreatedAt($this->tdate);
        }else{
            $quote->setWrappingType(null);
            $quote->setWrappingPrice(null);
            $model = Mage::getModel('wrappinggiftevent/custom_quote');
            $model->deteleByQuote($quote->getId());
        }        
    }
}
