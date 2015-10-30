<?php
/**
 * Description of Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */
abstract class Bilna_Customer_Model_Api2_Customer_Bilnacredit_Rest extends Bilna_Customer_Model_Api2_Customer_Bilnacredit
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
     * Retrieve bilna credit balance from table: 
     * - aw_points_summary
     *
     * @return array
     */
    protected function _retrieve()
    {
        $customerId = $this->_getCustomer($this->getRequest()->getParam('customer_id'));
        $creditBalance = $this->getCreditBalance($customerId);
        
        $limit = $this->getRequest()->getParam('limit');
        $page = $this->getRequest()->getParam('page');
        
        $creditHistory = $this->getCreditHistory($customerId, (($limit)?$limit:10), (($page)?$page:1));
        
        return array(
            'credit_balance' => $creditBalance, 
            'credit_history' => $creditHistory
        );
    }
}
