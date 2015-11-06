<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Producttrackinglink_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Producttrackinglink_Rest
{   
    protected function _retrieve()
    {
        //passing this params from api url
        //campaign_id=2&affiliate_id=2558&traffic_source_generate=&width_to_generate=120x600&category_to_generate=2&store_id=1&category_option_to_generate=null
        
    	$campaignId = $this->getRequest()->getParam('campaign_id');
        $data = $this->getRequest()->getParam('affiliate_id');
        
        
        
        var_dump($data['campaign_id']);die;
        $respon = $this->_generateHtmlLink($data);
        
        return array(
            'html' => $respon
        );
    }
    
    /** 
     * method to generate html link using curl, 
     * by accessing magento generator link, 
     * then read the content.
     * 
     * make it simple right?
     * we dont need to create code to generate html,
     * just use file_get_content of html link.
     * 
     * @link:
     * http://www.bilnaclone.com/affiliate/customer_affiliate/productsScript/campaign_id/2/affiliate_id/2558/traffic_source_generate//width_to_generate/120x600/category_to_generate/2/store_id/1/category_option_to_generate/null
     * 
     */
    private function _generateHtmlLink($data) {
        $url = Mage::getBaseUrl().'affiliate/customer_affiliate/productsScript/campaign_id/'.$data['campaign_id'].'/affiliate_id/'.$data['affiliate_id'].'/traffic_source_generate/'.$data['traffic_source_generate'].'/width_to_generate/'.$data['width_to_generate'].'/category_to_generate/'.$data['category_to_generate'].'/store_id/'.$data['store_id'].'/category_option_to_generate/'.$data['category_option_to_generate'];
        var_dump($url);die;
        return file_get_contents($url);
        
    }
}