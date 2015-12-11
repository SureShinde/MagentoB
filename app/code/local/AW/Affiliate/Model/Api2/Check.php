<?php

/**
 * affiliate api resource
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Bilna Development Team <core@magentocommerce.com>
 */
class AW_Affiliate_Model_Api2_Check extends Mage_Api2_Model_Resource
{
    public function _isAffiliateAccessible($affiliateId)
    {
        $_affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        if (is_null($_affiliate->getId())) {
            return false;
        }
        if ($_affiliate->getStatus() == AW_Affiliate_Model_Source_Affiliate_Status::INACTIVE) {
            return false;
        }
        return true;
    }

    public function _isCampaignAccessible($campaignId)
    {
        $_campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);
        return (!is_null($_campaign->getId())/* && $_campaign->isActive()*/);
    }

    public function _isAffiliateAllowedForCampaign($affiliateId, $campaignId, $checkDate = false)
    {
        $_campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);
        if ($checkDate) {
            $curTimestamp = Mage::app()->getLocale()->storeTimeStamp();
            if (!((is_null($_campaign->getActiveFrom()) OR (strtotime($_campaign->getActiveFrom()) < $curTimestamp)) &&
                (is_null($_campaign->getActiveTo()) OR (strtotime($_campaign->getActiveTo()) + 86400 > $curTimestamp)))
            ) {

                return false;
            }
        }
        return $_campaign->isAffiliateAllowed($affiliateId);
    }

    public function _isTrafficSourceAvailable($trafficId)
    {
        $_traffic = Mage::getModel('awaffiliate/traffic_source')->load($trafficId);
        return (!is_null($_traffic->getId()) ? $_traffic->getId() : false);
    }

    /*check duplicate object*/
    public function _isNewClientNotEqualCurrentClient($newClient, $currentClientId)
    {
        $client = Mage::getModel('awaffiliate/client')->load($currentClientId);
        //var_dump($newClient->getCampaignId());
        //var_dump($client->getCampaignId());
        //var_dump($newClient->getAffiliateId());
        //var_dump($client->getAffiliateId());
        //var_dump($newClient->getCampaignId() != $client->getCampaignId() ||
        //$newClient->getAffiliateId() != $client->getAffiliateId());
        //die;
        if (
            $newClient->getCampaignId() != $client->getCampaignId() ||
            $newClient->getAffiliateId() != $client->getAffiliateId()
        ) {
            return true;
        }
        return false;
    }

    private function _isCookieFree($cookieValue)
    {
        $client = Mage::getModel('awaffiliate/client')->load($cookieValue);
        $__isRewriteCookieEnabled = Mage::helper('awaffiliate/config')->isRewriteCookieEnabled();
        return is_null($client->getId()) || $__isRewriteCookieEnabled;
    }
}