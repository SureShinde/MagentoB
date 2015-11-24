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
    protected function _retrieve()
    {
        $cmid = $this->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY, null);
        $afid = $this->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY, null);
        $ats = $this->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE, null);
        $currentUrl = urldecode($this->getRequest()->getParam('current_url', null));
        //var_dump($currentUrl);die;
        if (!is_null($cmid) && !is_null($afid) && !is_null($ats)) {
            //params detect
            $campaignId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($cmid));
            $affiliateId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($afid));
            $trafficSourceId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($ats));
            $cookieModel = Mage::getSingleton('core/cookie');
            $clientId = $cookieModel->get(AW_Affiliate_Helper_Config::COOKIE_NAME);
            if (
                $this->_isCookieFree($clientId) &&
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
                    'customer_id' => Mage::getSingleton('customer/session')->getId()
                ));
                if ($this->_isNewClientNotEqualCurrentClient($newClient, $clientId)) {
                    try {
                        $newClient->save();
                        $cookieModel->set(AW_Affiliate_Helper_Config::COOKIE_NAME, $newClient->getId(), true); //set cookie on one year
                    } catch (Exception $e) {
                    }
                }
            }
            //redirect
            //$currentUrl = Mage::helper('core/url')->getCurrentUrl();
            $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($currentUrl, AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY);
            $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($redirectUrl, AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY);
            $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($redirectUrl, AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE);
            
            return array(
                'params' => array(
                    AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $cmid, 
                    AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $afid, 
                    AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $ats,
                    'redirect_url' => $redirectUrl
                ), 
            );
        }
    }

}