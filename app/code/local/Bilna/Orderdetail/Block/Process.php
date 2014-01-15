<?php
/**
 * Description of Process
 *
 * @path app/core/local/Bilna/Orderdetail/Block/Process.php
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Orderdetail_Block_Process extends Mage_Core_Block_Template {
    protected $_instructions;
    
    public function getOrder() {
        $orderData = $this->getRequest()->getPost('orderdetail');
        $orderEmail = $orderData['email'];
        $orderId = $orderData['orderid'];
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        
        if ($order->getEntityId()) {
            /**
             * check module gift wrapping
             */
            $giftWrappingActive = Mage::getConfig()->getModuleConfig('Bilna_Wrappinggiftevent')->is('active', true);
            
            if ($giftWrappingActive) {
                $model = Mage::getModel('wrappinggiftevent/custom_order');
                $price = $model->getByOrder($order->getId());

                if (isset ($price['wrapping_price'])) {
                    $order->setWrappinggiftevent($price['wrapping_price']);
                }
            }
            
            return $order;
        }
        
        return false;
    }

    public function getOrderId() {
        $orderData = $this->getRequest()->getPost('orderdetail');
        
        return $orderData['orderid'];
    }

    public function checkOrderEmail($orderEmail) {
        $orderData = $this->getRequest()->getPost('orderdetail');
        $email = $orderData['email'];

        if ($email == $orderEmail) {
            return true;
        }

        return false;
    }

    public function getGuestOrderFormUrl() {
        return sprintf("%sorderdetail/index/", Mage::getBaseUrl());
    }
    
    public function getPayUrl($orderId) {
        return sprintf("%sklikpay/processing/pay/id/%s/", Mage::getBaseUrl(), $orderId);
    }
    
    public function getLoginRegisterUrl() {
        return sprintf("%scustomer/account/login/", Mage::getBaseUrl());
    }
    
    public function getOrderStatusTitle($orderStatus) {
        $statuses = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        $result = '';
        
        foreach ($statuses as $row) {
            if ($row['status'] == strtolower($orderStatus)) {
                $result = $row['label'];
                break;
            }
        }
        
        return $result;
    }
    
    public function getPaymentInfoHtml() {
        return $this->getChildHtml('payment_info');
    }
    
    /**
     * Get instructions text from order payment
     * (or from config, if instructions are missed in payment)
     *
     * @return string
     */
    public function getInstructions() {
        if (is_null($this->_instructions)) {
            $this->_instructions = $this->getInfo()->getAdditionalInformation('instructions');
            
            if (empty ($this->_instructions)) {
                $this->_instructions = $this->getMethod()->getInstructions();
            }
        }
        
        return $this->_instructions;
    }
}
