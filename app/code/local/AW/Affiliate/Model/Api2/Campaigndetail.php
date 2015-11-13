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
    const DEFAULT_STORE_ID = 1;

    public function __construct() 
    {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }

    private function getCampaignCollectionByCampaignId($campaignId = null)
    {
        if (!$this->_campaignsCollection instanceof AW_Affiliate_Model_Resource_Campaign_Collection) {
            $__group = Mage::registry('current_affiliate')->getCustomerGroupId();
            $this->_campaignsCollection = Mage::getModel('awaffiliate/campaign')->getCollection();
            $this->_campaignsCollection
                ->joinProfitCollection()
                ->addFilterById($campaignId)
                ->addFilterByWebsite(Mage::app()->getWebsite()->getId())
                ->addFilterByCustomerGroup($__group)
                ->addStatusFilter()
                ->addDateFilter()
                ->setOrder('active_to ', 'DESC');
        }
        return $this->_campaignsCollection;
    }
}