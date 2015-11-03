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

        $campaignCollection = array();
        $withdrawCollection = array();
                
        $blockCampaign = new AW_Affiliate_Block_Customer_Campaigns();
        $blockWithdraw = new AW_Affiliate_Block_Customer_Withdrawal();            
        
    	try{
    		$affiliate = $this->__getAffiliate($affiliateId);
    		if($affiliate->getId())
    		{
    			$availableBalance = $this->getActiveBalance();
    			$totalEarnings = $this->getCurrentBalance();
    			$lifeTimeEarnings = $this->getTotalAffiliated();
    			$campaignCollection = $this->getCampaignCollection();
    			$withdrawCollection = $blockWithdraw->getWithdrawalCollection();
    		}

    	}catch(Exception $e){
    		$this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
    	}

        if(!empty($campaignCollection)) {
            $campaign = array();
            
            foreach($campaignCollection as $item) {
                $campaign[] = array(
                    'entity_id' => $item->getId(), 
                    'name' => $item->getName(), 
                    'campaign_type' => $item->getCampaignType(), 
                    'rate' => $blockCampaign->getRate($item), 
                    'earning' => $blockCampaign->getTotalAmountByCampaignId($item->getId()), 
                    'status' => $blockCampaign->getCampaignStatusLabel($item->getStatus())
                );
            }
        }

        if(!empty($withdrawCollection)) {
            $withdraw = array();
            
            foreach($withdrawCollection as $item) {
                $withdraw[] = array(
                    'entity_id' => $item->getId(), 
                    'amount' => $blockWithdraw->formatCurrency($item->getAmount()), 
                    'date' => $blockWithdraw->formatDate($item->getCreatedAt()), 
                    'status' => $blockWithdraw->getStatusLabel($item)
                );
            }
        }
        
    	return array(
    		'summary' => array(
    			'available_balance' => $availableBalance,
    			'total_earnings'    => $totalEarnings,
    			'life_time_earnings'=> $lifeTimeEarnings
    		),
    		'campaign' => $campaign,
    		'withdraw' => $withdraw
    	);
    }

}