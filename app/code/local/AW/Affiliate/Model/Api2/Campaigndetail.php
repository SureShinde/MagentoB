<?php

/**
 * affiliate api resource
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Bilna Development Team <core@magentocommerce.com>
 */
class AW_Affiliate_Model_Api2_Campaigndetail extends Mage_Api2_Model_Resource
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
    
    protected function getCampaignCollection($campaignId = null)
    {        
        $__group = Mage::registry('current_affiliate')->getCustomerGroupId();
        $_campaignsCollection = Mage::getModel('awaffiliate/campaign')->getCollection();
        $_campaignsCollection
            ->joinProfitCollection()
            ->addFilterById($campaignId)
            ->addFilterByWebsite(self::DISTRO_STORE_ID)
            ->addFilterByCustomerGroup($__group)
            ->addStatusFilter()
            ->addDateFilter();
        
        if($_campaignsCollection->getSize())
        {
	        return $_campaignsCollection;
        }

        return false;
    }
}