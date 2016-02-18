<?php
/**
 * Description of Bilnacredit
 *
 * @author Bilna Development Team <development@bilna.com>
 * 
 * @link http://mandagreen.com/showing-all-reviews-and-ratings-on-a-page-in-magento/
 * @link https://wiki.magento.com/display/m1wiki/Using+Magento+1.x+collections
 * @link http://devdocs.magento.com/guides/m1x/magefordev/mage-for-dev-8.html
 * 
 */

class Bilna_Customer_Model_Api2_Customer_Bilnacredit extends Mage_Api2_Model_Resource
{
    /** 
     * Get customer credit balance, based on:
     * /app/design/frontend/base/default/template/aw_points/customer/mag14/reward/summary.phtml
     * /app/code/local/AW/Points/Block/Customer/Reward/Summary.php
     * 
     * AW_Points_Block_Customer_Reward_Summary::_construct()
     * 
     * @param integer $customerId
     * @return array
     */
    protected function getCreditBalance($customerId = null) 
    {
        $summary = Mage::getModel('points/summary')
                        ->loadByCustomerID($customerId);
        return $summary->getData();
    }
    
    /** 
     * Get all customer credit history, based on:
     * /app/design/frontend/base/default/template/aw_points/customer/mag14/reward/history.phtml
     * /app/code/local/AW/Points/Block/Customer/Reward/History.php
     * 
     * AW_Points_Block_Customer_Reward_History::__construct()
     * 
     * @param integer $customerId
     * @param integer $pageSize
     * @param integer $curPage
     * @return array
     */
    protected function getCreditHistory($customerId = null, $pageSize = 10, $curPage = 1)
    {   
        //load customer object
        $customer = Mage::getModel('customer/customer')->load($customerId);
        
        $collection = Mage::getModel('points/api')
                        ->getCustomerTransactions($customer)
                        ->setOrder('change_date', 'DESC')
                        ->setPageSize($pageSize)
                        ->setCurPage($curPage);
        
        return $collection->getData();
    }
}
