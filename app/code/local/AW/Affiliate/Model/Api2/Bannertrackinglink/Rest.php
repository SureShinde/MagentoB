<?php
/**
 * Description of Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */
abstract class AW_Affiliate_Model_Api2_Bannertrackinglink_Rest extends AW_Affiliate_Model_Api2_Bannertrackinglink
{
    protected function _initAffiliate($affiliateId)
    {
        $affiliate = NULL;

        if ($affiliateId) {
            $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        }

        return $affiliate;
    }

    protected function _initCampaign($campaignId)
    {
        $campaign = NULL;

        if($campaignId) {
            $campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);
        }
        if (!$campaign->getId()) {
            $this->_critical("Couldn't load compaign by given id");
        }

        return $campaign;
    }
}