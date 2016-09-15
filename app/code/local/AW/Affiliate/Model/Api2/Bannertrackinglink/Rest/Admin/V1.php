<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Bannertrackinglink_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Bannertrackinglink_Rest
{
    protected function _retrieve()
    {
        //passing this params from api url
        //campaign_id=6&affiliate_id=2558&traffic_source_generate=blaBlablaBla&link_to_generate=http://www.orami.co.id/

    	$campaignId = $this->getRequest()->getParam('campaign_id');
        $affiliateId = $this->getRequest()->getParam('affiliate_id');
        $trafficSourceGenerate = $this->getRequest()->getParam('traffic_source_generate');
        $linkToGenerate = $this->getRequest()->getParam('link_to_generate');

        $affiliate = $this->_initAffiliate($affiliateId);
        $campaign = $this->_initCampaign($campaignId);

        if (is_null($affiliate)) {
            $this->_critical('Unable to get the affiliate ID');
        }

        if (is_null($campaign)) {
            $this->_critical('Unable to get the campaign ID');
        }

        if (empty($linkToGenerate) || (strlen($linkToGenerate) == 0)) {
            $this->_critical('Tracking Link is not specified');
        }

        $collection = Mage::getModel('awaffiliate/traffic_source')->getCollection();
        $collection->addFieldToFilter('main_table.traffic_name', array("eq" => $trafficSourceGenerate));
        $collection->addFieldToFilter('main_table.affiliate_id', array("eq" => $affiliate->getId()));
        $collection->setPageSize(1);

        if (!$collection->getSize()) {
            $trafficItem = Mage::getModel('awaffiliate/traffic_source');
            $trafficItem->setData(array(
                'affiliate_id' => $affiliate->getId(),
                'traffic_name' => $trafficSourceGenerate
            ));
            $trafficItem->save();
            $trafficId = $trafficItem->getId();
        } else {
            $trafficId = $collection->getFirstItem()->getId();
        }

        $baseUrl = trim($trackingLink);
        $params = array(
            AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $campaign->getId(),
            AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $affiliate->getId(),
            AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $trafficId
        );
        $resultUrl = Mage::helper('awaffiliate/affiliate')->generateAffiliateLink($baseUrl, $params);
        $bannerImage = $campaign->getImageName();

        return array(
            'link' => $resultUrl,
            'banner' => $bannerImage
        );
    }
}