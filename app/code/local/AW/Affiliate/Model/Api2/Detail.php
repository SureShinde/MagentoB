<?php

/**
 * affiliate api resource
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Bilna Development Team <core@magentocommerce.com>
 */
class AW_Affiliate_Model_Api2_Customer extends Mage_Api2_Model_Resource
{
	/**
     * Default store Id (for install)
     */
    const DISTRO_STORE_ID       = 1;

    /**
     * Default store code (for install)
     *
     */
    const DISTRO_STORE_CODE     = 'default';
	/**
     * Application store object
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;
	/**
	 *
	 * 
	 *
	 **/
	protected function __getAffiliate($affiliateId)
	{
		$affiliate = Mage::getModel('awaffiliate/affiliate');

        if ($affiliateId) {
            $affiliate->loadByCustomerId($affiliateId);
        }else{
        	Mage::throwException('Affiliate ID is empty');
        }
        Mage::register('current_affiliate', $affiliate);
        return $affiliate;
	}

	protected function getActiveBalance()
    {
        $requestWithdrawal = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
        $requestWithdrawal->addFieldToFilter('status', array('eq' => AW_Affiliate_Model_Source_Withdrawal_Status::PENDING));
        $requestWithdrawal->addAffiliateFilter(Mage::registry('current_affiliate')->getId());
        $activeBalance = Mage::registry('current_affiliate')->getActiveBalance();
        foreach ($requestWithdrawal as $item) {
            $activeBalance -= $item->getAmount();
        }
        return Mage::helper('core')->formatCurrency($activeBalance);

    }

    protected function getCurrentBalance()
    {
        $currentBalance = Mage::registry('current_affiliate')->getCurrentBalance();
        return Mage::helper('core')->formatCurrency($currentBalance);
    }

    protected function getTotalAffiliated()
    {
        $totalAffiliated = Mage::registry('current_affiliate')->getTotalAffiliated();
        return Mage::helper('core')->formatCurrency($totalAffiliated);
    }

    protected function getCampaignCollection()
    {        
        $__group = Mage::registry('current_affiliate')->getCustomerGroupId();
        $_campaignsCollection = Mage::getModel('awaffiliate/campaign')->getCollection();
        $_campaignsCollection
            ->joinProfitCollection()
            ->addFilterByWebsite(self::DISTRO_STORE_ID)
            ->addFilterByCustomerGroup($__group)
            ->addStatusFilter()
            ->addDateFilter()
            ->setOrder('active_to ', 'DESC');
        
        $_campaigns = array();
        
        if($_campaignsCollection->getSize())
        {
	        foreach ($_campaignsCollection as $item) 
	        {
	        	$_campaigns[] = $item;
	        }
	    }

        return $_campaigns;
    }

    protected function _getDefaultStore()
    {
        if (empty($this->_store)) {
            $this->_store = Mage::getModel('core/store')
                ->setId(self::DISTRO_STORE_ID)
                ->setCode(self::DISTRO_STORE_CODE);
        }
        return $this->_store;
    }
}