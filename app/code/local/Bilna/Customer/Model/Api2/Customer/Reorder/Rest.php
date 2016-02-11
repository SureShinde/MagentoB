<?php
/**
 * Description of Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */
abstract class Bilna_Customer_Model_Api2_Customer_Reorder_Rest extends Bilna_Customer_Model_Api2_Customer_Reorder
{
    /**
     *
     */
    protected function _getCustomer($customerId)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->load($customerId);
        if (!$customer->getId()) {
            throw Mage::throwException('Customer Not Exists');
        }
        return $customerId;
    }
    
    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null, $customerId)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
        }
        if (!$orderId) {
            throw Mage::throwException('Order Id Not Exists');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        if ($this->_canViewOrder($order, $customerId)) {
            return true;
        } else {
            return false;
        }
        return false;
    }
    
    /**
     * Check order view availability
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  bool
     */
    protected function _canViewOrder($order, $customerId)
    {
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }
}
