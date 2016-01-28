<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Customer_Reorder_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Customer_Reorder_Rest
{
    //path: http://www.bilnaclone.com/api/rest/customers/842/reorder/244570
    protected function _retrieve()
    {
        $customerId = $this->_getCustomer($this->getRequest()->getParam('customer_id'));
        $orderId = $this->getRequest()->getParam('order_id');
        
        if (!$this->_loadValidOrder($orderId, $customerId)) {
            throw Mage::throwException('Invalid order ID');
        }
        $cart = Mage::getSingleton('checkout/cart');
        $order = Mage::getModel('sales/order')->load($orderId);
        
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Mage_Core_Exception $e){
                throw Mage::throwException($e->getMessage());
            }
        }
    }
}
