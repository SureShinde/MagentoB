<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Customer_Productitem_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Customer_Productitem_Rest
{

    /**
     * Retrieve bilna credit balance from table: 
     * - aw_points_summary
     *
     * @return array
     */
    protected function _retrieve()
    {
        $customerId = $this->_getCustomer($this->getRequest()->getParam('customer_id'));
        $itemId = $this->getRequest()->getParam('item_id');
        $orderItem = Mage::getModel('sales/order_item')->load($itemId);
        
        if($orderItem->getProductId()) {
            return array(
                'item' => $orderItem->getData()
            );
        }
    }
}
