<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Customer_View_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Customer_View_Rest
{
	protected function _retrieve()
    {
    	$affiliateId = $this->getRequest()->getParam('id'); //customer-id

    	try{
    		$affiliate = $this->__getAffiliate($affiliateId);

    		if($affiliate->getId())
    		{
    			$availableBalance = $this->getActiveBalance();
    			$totalEarnings = $this->getCurrentBalance();
    			$lifeTimeEarnings = $this->getTotalAffiliated();
    			$campaignCollection = $this->getCampaignCollection();
    		}

    	}catch(Exception $e){
    		$this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
    	}

    	return array(
    		'summary' => array(
    			'available_balance' => $availableBalance,
    			'total_earnings'    => $totalEarnings,
    			'life_time_earnings'=> $lifeTimeEarnings
    		),
    		'campaign' => $campaignCollection
    	);
    }

}