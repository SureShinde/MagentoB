<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Campaigndetail_View_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Campaigndetail_View_Rest
{
    protected function _retrieve()
    {
    	$campaignId = $this->getRequest()->getParam('id'); //campaign-id
        $campaignCollection = array();
                
        $blockCampaign = new AW_Affiliate_Block_Customer_Campaigns();
        
    	try{
    		$campaignDetail = $blockCampaign->getCampaignCollectionByCampaignId($campaignId);
    		
                if($campaignDetail->getId())
    		{
                    $campaignCollection = $campaignDetail->getData();
    		}

    	}catch(Exception $e){
    		$this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
    	}
        
        return array(
            'campaign_detail' => $campaignCollection
        );
    }

}