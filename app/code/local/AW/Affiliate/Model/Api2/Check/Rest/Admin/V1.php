<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Check_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Check_Rest
{
    //example path:
    //http://www.bilnaclone.com/api/rest/affiliate/check?customer_id=95504&cmid=NWl6WlpHZ3FxVFk9&afid=dUMyemkrY1YyU2c9&ats=V0kwS2tCekNITU09&current_url=http%3A%2F%2Fwww.bilnaclone.com%2Fregal-marie-baru-klg-550g.html%3Fcmid%3DNWl6WlpHZ3FxVFk9%26afid%3DdUMyemkrY1YyU2c9%26ats%3DV0kwS2tCekNITU09
    protected function _retrieve()
    {
        $cmid = $this->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY, null);
        $afid = $this->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY, null);
        $ats = $this->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE, null);
        $customerId = $this->getRequest()->getParam('customer_id', null);
        $currentUrl = urldecode($this->getRequest()->getParam('current_url', null));
        
        //var_dump($currentUrl);die;
        if (!is_null($cmid) && !is_null($afid) && !is_null($ats)) {
            //params detect
            $campaignId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($cmid));
            $affiliateId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($afid));
            $trafficSourceId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($ats));
            $cookieModel = Mage::getSingleton('core/cookie');
            $clientId = $cookieModel->get(AW_Affiliate_Helper_Config::COOKIE_NAME);
            
            $client = null;
            $affiliate = null;
            $redirectUrl = null;
            //var_dump($this->_isCampaignAccessible($campaignId));die;
            if (
                //$this->_isCookieFree($clientId) && //disabled cookie, since cookie will generate by logan
                $this->_isAffiliateAccessible($affiliateId) &&
                $this->_isCampaignAccessible($campaignId) &&
                $this->_isTrafficSourceAvailable($trafficSourceId) &&
                $this->_isAffiliateAllowedForCampaign($affiliateId, $campaignId)
            ) {
                $newClient = Mage::getModel('awaffiliate/client');
                $newClient->setData(array(
                    'campaign_id' => $campaignId,
                    'affiliate_id' => $affiliateId,
                    'traffic_id' => $trafficSourceId,
                    'customer_id' => $customerId //customer id that access the affiliate link, can be null or integer
                ));
                
                //var_dump($clientId);
                //var_dump($newClient->getId());
                //var_dump($this->_isNewClientNotEqualCurrentClient($newClient, $clientId));die;
                
                if ($this->_isNewClientNotEqualCurrentClient($newClient, $clientId)) {
                    try {
                        $newClient->save();
                        //$cookieModel->set(AW_Affiliate_Helper_Config::COOKIE_NAME, $newClient->getId(), true); //set cookie on one year
                        $client = array(
                            'id' => $newClient->getId(), 
                            'campaign_id' => $campaignId,
                            'affiliate_id' => $affiliateId,
                            'traffic_id' => $trafficSourceId,
                            'customer_id' => $customerId
                        ); 
                        $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
                        $affiliate = $affiliate->getData();
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                } else {
                    $client = array(
                        'id' => $clientId, 
                        'campaign_id' => $campaignId,
                        'affiliate_id' => $affiliateId,
                        'traffic_id' => $trafficSourceId,
                        'customer_id' => $customerId
                    ); 
                }
                
                $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($currentUrl, AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY);
                $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($redirectUrl, AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY);
                $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($redirectUrl, AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE);
            }
            
            return array(
                'params' => array(
                    AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $cmid, 
                    AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $afid, 
                    AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $ats
                ), 
                'client' => $client, 
                'affiliate' => $affiliate, 
                'redirect_url' => $redirectUrl
            );
        }
        
        Mage::throwException('Pleas provide all parameter to process affiliate.');
    }

}