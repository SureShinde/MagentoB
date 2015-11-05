<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Campaigndetail_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Campaigndetail_Rest
{
    protected function _retrieve()
    {
    	$campaignId = $this->getRequest()->getParam('id'); //campaign-id
        $campaignCollection = array();
        
    	$campaignDetail = $this->getCampaignCollectionByCampaignId($campaignId);
            
        if($campaignDetail->getId())
        {
            $campaignCollection = $campaignDetail->getData();
        
            return array(
                'campaign_detail' => $campaignCollection
            );
        }
    }

    private function getCampaignCollectionByCampaignId($campaignId = null)
    {
        $campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);
        return $campaign;
    }
}