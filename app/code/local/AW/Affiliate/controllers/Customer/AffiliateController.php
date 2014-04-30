<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Customer_AffiliateController extends Mage_Core_Controller_Front_Action
{
    protected function _initAffiliate()
    {
        $customerId = Mage::getSingleton('customer/session')->getId();
        $affiliate = Mage::getModel('awaffiliate/affiliate');

        if ($customerId) {
            $affiliate->loadByCustomerId($customerId);
        }

        Mage::register('current_affiliate', $affiliate);
        return $this;
    }

    protected function _initCampaign($paramKey = 'id')
    {
        $campaignId = $this->getRequest()->getParam($paramKey);
        $campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);

        if (is_null($campaign->getId())) {
            $this->_getSession()->addError($this->__("Couldn't load compaign by given id"));
            return $this->_redirect('*/*/view');
        }
        Mage::register('current_campaign', $campaign);
        return $this;
    }

    protected function _initAction($title = 'Magento Affiliate')
    {
        // Redirecting to login page when there is no authorized customer
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, TRUE);
        }
        if (is_null(Mage::registry('current_affiliate')->getId())) {
            $this->_redirect('customer/account/index');
            return $this;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__($title));

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('awaffiliate/customer_affiliate/view');
        }
        return $this;
    }

    protected function indexAction()
    {
        $this->_redirect('*/*/view');
    }

    protected function viewAction()
    {
        $this->_initAffiliate()
            ->_initAction($this->__('Affiliate Program'))
            ->renderLayout();
        return;
    }

    protected function campaignAction()
    {
        $this->_initAffiliate()
            ->_initCampaign();
        if (!$this->_hasErrors()) {
            $campaign = Mage::registry('current_campaign');
            $this->_initAction($campaign->getName())
                ->renderLayout();
        }
        return;
    }

    protected function campaignproductsAction()
    {    
        $this->_initAffiliate()
            ->_initCampaign();
        if (!$this->_hasErrors()) {
            $campaign = Mage::registry('current_campaign');
            $this->_initAction($campaign->getName())
                ->renderLayout();
        }
        return;
    }

    protected function reportAction()
    {
        Mage::helper('awaffiliate')->updatePrototypeJS();
        $this->_initAffiliate()
            ->_initAction($this->__('Reports'))
            ->renderLayout();
        return;
    }

    protected function downloadreportAction()
    {
        $filename = $this->__('Report_as_CSV') . '.csv';
        $this->getResponse()->setHeader('Content-Type', 'application/octet-stream');
        $this->getResponse()->setHeader('Content-Disposition', "attachment; filename=\"" . $filename . "\"");
        $this->getResponse()->setHeader('Content-Transfer-Encoding', 'binary');

        $this->_initAffiliate();
        $gridData = Mage::getSingleton('customer/session')->getAffiliateGridForDownload();
        $csvContent = '';
        $element = current($gridData);
        if (is_null($element)) {
            $this->getResponse()->setBody($csvContent);
            return;
        }
        $data = array();
        foreach (array_keys($element) as $key) {
            $data[] = "\"" . $key . "\"";
        }
        $csvContent .= implode(',', $data) . "\n";
        foreach ($gridData as $row) {
            $data = array();
            foreach ($row as $field) {
                $data[] = "\"" . str_replace(array("\"", '\\'), array("\"\"", '\\\\'), $field) . "\"";
            }
            $csvContent .= implode(',', $data) . "\n";
        }
        $this->getResponse()->setBody($csvContent);
        return;
    }

    protected function withdrawalRequestCreateAction()
    {
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Customer is not logged in'));
            $this->_redirect('customer/account/login');
            return;
        }

        $isError = false;
        $this->_initAffiliate();
        $affiliate = Mage::registry('current_affiliate');

        $affiliateId = intval($affiliate->getId());
        if ($affiliateId < 1) {
            $isError = true;
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Unable to get the affiliate ID'));
        }
        $amount = intval($this->getRequest()->getParam('amount', null));
        if (is_null($amount) || ($amount < 1)) {
            $isError = true;
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Incorrect amount'));
        }

        if (!$isError && !Mage::helper('awaffiliate/affiliate')->isWithdrawalRequestAvailableOn($affiliate, $amount)) {
            $isError = true;
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('This amount is not available for request'));
        }
        if (Mage::helper('awaffiliate/config')->getMinimumAmountToWithdraw(Mage::app()->getStore(true)->getId()) > $amount) {
            $isError = true;
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Requested amount is insufficient to withdraw. Minimal request amount is %d %s',
                Mage::helper('awaffiliate/config')->getMinimumAmountToWithdraw(Mage::app()->getStore(true)->getId()), Mage::app()->getBaseCurrencyCode()));
        }
        if (!$isError) {
            $withdrawalRequest = Mage::getModel('awaffiliate/withdrawal_request');
            $withdrawalRequest->setAmount($amount);
            $withdrawalRequest->setDescription(strip_tags($this->getRequest()->getParam('details')));
            $withdrawalRequest->setAffiliateId($affiliateId);
            $withdrawalRequest->setData('created_at', Mage::getModel('core/date')->gmtDate());
            try {
                $withdrawalRequest->save();
                $this->_getSession()->addSuccess(Mage::helper('awaffiliate')->__('Withdrawal request saved'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->setWithdrawalRequestCreateFormData($this->getRequest()->getParams());
        }
        $__defaultUrl = "awaffiliate/customer_affiliate/view";
        $this->_redirectReferer($__defaultUrl);
    }

    protected function getReportAsJsonAction()
    {
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Customer is not logged in'));
            $this->_redirect('customer/account/login');
            return;
        }

        $postData = $this->getRequest()->getParams();
        $this->_getSession()->setCreateReportFormData($postData);

        $messages = array();
        $response = new Varien_Object();
        $response->setError(0);
        $this->_initAffiliate();
        $affiliate = Mage::registry('current_affiliate');

        $affiliateId = intval($affiliate->getId());
        if ($affiliateId < 1) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Unable to get the affiliate ID');
        }

        if (!isset($postData['report_type'])) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Report type is not specified');
        }

        if ($response->getError() == 0) {
            if ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::SALES) {
                $block = $this->getLayout()->createBlock('awaffiliate/report_view_sales');
                $block->addData($postData);
                $response->setHtml($block->toHtml());
            } elseif ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::TRANSACTIONS) {
                $block = $this->getLayout()->createBlock('awaffiliate/report_view_transactions');
                $block->addData($postData);
                $response->setHtml($block->toHtml());
            } elseif ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::TRAFFIC) {
                $block = $this->getLayout()->createBlock('awaffiliate/report_view_traffic');
                $block->addData($postData);
                $response->setHtml($block->toHtml());
            } else {
                $response->setError(1);
                $messages[] = Mage::helper('awaffiliate')->__('Invalid report type');
            }
        }
        $response->setMessages($messages);
        $this->getResponse()->setBody($response->toJson());
        return;
    }

    protected function generateLinkAction()
    {
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Customer is not logged in'));
            $this->_redirect('customer/account/login');
            return;
        }
        $postData = $this->getRequest()->getParams();
        $this->_getSession()->setGenerateLinkFormData($postData);

        $messages = array();
        $response = new Varien_Object();
        $response->setError(0);
        $this->_initAffiliate();
        $this->_initCampaign('campaign_id');

        $affiliate = Mage::registry('current_affiliate');
        if (intval($affiliate->getId()) < 1) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Unable to get the affiliate ID');
        }

        $campaign = Mage::registry('current_campaign');
        if (is_null($campaign)) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Unable to get the campaign ID');
        }

        if (!isset($postData['link_to_generate']) || (strlen($postData['link_to_generate']) == 0)) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Tracking Link is not specified');
        }

        if ($response->getError() == 0) {
            $collection = Mage::getModel('awaffiliate/traffic_source')->getCollection();
            $collection->addFieldToFilter('main_table.traffic_name', array("eq" => $postData['traffic_source_generate']));
            $collection->addFieldToFilter('main_table.affiliate_id', array("eq" => $affiliate->getId()));
            $collection->setPageSize(1);

            if (!$collection->getSize()) {
                $trafficItem = Mage::getModel('awaffiliate/traffic_source');
                $trafficItem->setData(array(
                    'affiliate_id' => $affiliate->getId(),
                    'traffic_name' => $postData['traffic_source_generate']
                ));
                $trafficItem->save();
                $trafficId = $trafficItem->getId();
            } else {
                $trafficId = $collection->getFirstItem()->getId();
            }
        }

        if ($response->getError() == 0) {
            $baseUrl = trim($postData['link_to_generate']);
            $params = array(
                AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $campaign->getId(),
                AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $affiliate->getId(),
                AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $trafficId
            );
            $resultUrl = Mage::helper('awaffiliate/affiliate')->generateAffiliateLink($baseUrl, $params);
            $response->setData('result', $resultUrl);
        }

        $campaignImageName  = Mage::registry('current_campaign')->getImageName();
        $response->setData('image', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .$campaignImageName);


        $response->setMessages($messages);
        $this->getResponse()->setBody($response->toJson());
        return;
    }

    private function _getSession()
    {
        return Mage::getSingleton('core/session');
    }

    protected function _hasErrors()
    {
        return (bool)count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    public function productsAction()
    {
        $data = $this->getRequest()->getPost();

        $this->_getSession()->setGenerateLinkFormData($data);

        $messages = array();
        $response = new Varien_Object();
        $response->setError(0);
        $this->_initAffiliate();
        $this->_initCampaign('campaign_id');
        $campaign = Mage::registry('current_campaign');
        $affiliate = Mage::registry('current_affiliate');
        /*
        Array
        (
            [numOfProductsToGenerate] => 6
            [categoryToGenerate] => 1
        )
        */
        if ($response->getError() == 0) {
            $collection = Mage::getModel('awaffiliate/traffic_source')->getCollection();
            $collection->addFieldToFilter('main_table.traffic_name', array("eq" => $data['traffic_source_generate']));
            $collection->addFieldToFilter('main_table.affiliate_id', array("eq" => $affiliate->getId()));
            $collection->setPageSize(1);

            if (!$collection->getSize()) {
                $trafficItem = Mage::getModel('awaffiliate/traffic_source');
                $trafficItem->setData(array(
                    'affiliate_id' => $affiliate->getId(),
                    'traffic_name' => $data['traffic_source_generate']
                ));
                $trafficItem->save();
                $trafficId = $trafficItem->getId();
            } else {
                $trafficId = $collection->getFirstItem()->getId();
            }
        }

        switch ($data['categoryToGenerate']) {
            case 1:
                $_block = $this->getLayout()->getBlockSingleton('awaffiliate/campaign_product_list');

                $collection = $_block->getProductCollection($data['numOfProductsToGenerate']);

                if($collection->count())
                {
                    foreach ($collection as $_product)
                    {
                        $baseUrl = trim($_product->getProductUrl());
                        $params = array(
                            AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $campaign->getId(),
                            AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $affiliate->getId(),
                            AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $trafficId
                        );
                        $resultUrl = Mage::helper('awaffiliate/affiliate')->generateAffiliateLink($baseUrl, $params);

                        $p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" width="44" height="44" /></a>';
                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="http://demo.mage-world.com/1501/media/catalog/product/cache/1/thumbnail/44x44/9df78eab33525d08d6e5fb8d27136e95/h/t/htc-touch-diamond.jpg" width="44" height="44" /></a>';
                        $p[$_product->getId()]['price'] = $_product->getPrice(); //$this->getLayout()->getBlock('product.info')->getPriceHtml($_product, true);/   
                    }
                    $messages[] = 'success';
                }else{

                    $messages[] = 'fail';
                }
                break;
            
            case 2:

                break;
            
            default:
                # code...
                break;
        }

        //$collection;
        $response->setData('result', $p);
        $response->setData('msg', 'success');
        $response->setMessages($collection);
        $this->getResponse()->setBody($response->toJson());
        return;
    }

    public function productsScriptAction()
    {
        $data = $this->getRequest()->getParams();
        /*later we can change to DB, but we need it ?????*/
        switch ($data['width_to_generate']) {
            case '299x250':
                $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/medium_rectangel.phtml');
                break;
            case '728x90':
                $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/leaderboard.phtml');
                break;
            case '468x60':
                $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/full_banner.phtml');
                break;
            case '320x50':
                $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/mobile_banner.phtml');
                break;
            case '160x600':
                $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/wide_skyscraper.phtml');
                break;
            case '120x600':
                $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/skyscraper.phtml');
                break;
            case '299x600':
                $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/half_page.phtml');
                break;
            default:
                $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/leaderboarc.phtml');
                break;
        }

        $this->getResponse()->setBody($block->toHtml());

        return;
    }

    public function productsScriptAction_backup()
    {
        $block = $this->getLayout()->createBlock('awaffiliate/campaign_products')->setTemplate('aw_affiliate/ads/banner.phtml');

        

        $data = $this->getRequest()->getParams();
//print_r($data['num_of_products_to_generate']);
        $response = new Varien_Object();
        $response->setError(0);
//        $this->_initAffiliate();
//        $this->_initCampaign('campaign_id');
//        $campaign = Mage::registry('current_campaign');
//        $affiliate = Mage::registry('current_affiliate');
//echo $affiliate->getId();
        if ($response->getError() == 0) {
            $collection = Mage::getModel('awaffiliate/traffic_source')->getCollection();
            $collection->addFieldToFilter('main_table.traffic_name', array("eq" => $data['traffic_source_generate']));
            $collection->addFieldToFilter('main_table.affiliate_id', array("eq" => $data['affiliate_id']));
            $collection->setPageSize(1);

            if (!$collection->getSize()) {
                $trafficItem = Mage::getModel('awaffiliate/traffic_source');
                $trafficItem->setData(array(
                    'affiliate_id' => $data['affiliate_id'], //$affiliate->getId(),
                    'traffic_name' => $data['traffic_source_generate']
                ));
                $trafficItem->save();
                $trafficId = $trafficItem->getId();
            } else {
                $trafficId = $collection->getFirstItem()->getId();
            }
        }
//echo $data['category_to_generate'];
        $helper = Mage::helper('adminhtml');
        switch ($data['category_to_generate']) {
            case 1:
                $_block = $this->getLayout()->getBlockSingleton('awaffiliate/campaign_product_list');

                $collection = $_block->getProductCollection($data['num_of_products_to_generate']);

                if($collection->count())
                {
                    foreach ($collection as $_product)
                    {
                        $baseUrl = trim($_product->getProductUrl());
                        $params = array(
                            AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $data['campaign_id'], //$campaign->getId(),
                            AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $data['affiliate_id'], //$affiliate->getId(),
                            AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $trafficId
                        );
                        $resultUrl = Mage::helper('awaffiliate/affiliate')->generateAffiliateLink($baseUrl, $params);

                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" width="44" height="44" /></a>';
                        $p['id']   = $_product->getId();
                        $p['a']    = $resultUrl;
                        $p['img']  = '<img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" width="44" height="44" style="z-index: 200" />';//Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135);
                        $p['name'] = $helper->stripTags($_product->getName());
                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="http://demo.mage-world.com/1501/media/catalog/product/cache/1/thumbnail/44x44/9df78eab33525d08d6e5fb8d27136e95/h/t/htc-touch-diamond.jpg" width="44" height="44" /></a>';
                        $p['price'] = $_product->getPrice();//$helper->getPriceHtml($_product->getPrice()); //$this->getLayout()->getBlock('product.info')->getPriceHtml($_product, true);/   
                        $x[] = $p;
                    }
                    $messages[] = 'success';
                }else{

                    $messages[] = 'fail';
                }
                break;
            
            case 2:

                break;
            
            default:
                # code...
                break;
        }
//print_r($p);

        $config = array(
            'campaign_id'       => $data['campaign_id'],
            'affiliate_id'      => $data['affiliate_id'],
            'traffic_source_generate'=> $data['traffic_source_generate'],
            'width_to_generate' => $data['width_to_generate'],
            'category_to_generate'=> $data['category_to_generate'],
            'num_of_products_to_generate'=>$data['num_of_products_to_generate'],
            'num'               => count($x)
        );
//print_r($p);
        $s = <<<EOT
var AFF={};AFF.config=__CONFIG__;var ads=__ADS__;
var Bilna = {
    status: 0,
    print: function(){
        var thebody = document.createElement("tbody");
        thebody.class="map_table";
        var i=0;
        var w = Math.round(AFF.config.width_to_generate/50) - 1;
        var col = (AFF.config.num < w) ? AFF.config.num : w;

        var row = Math.round(AFF.config.num/col) + 1;
        for (var x = 0; x < row; x++)
        {
            var newRow = document.createElement("tr");
            for (var y = 0; y < col; y++)
            {
                var newCell = document.createElement("td");
                newCell.setAttribute("style",'');
                if (ads[i])
                {
                    newCell.innerHTML=('<div><a href="'+ads[i].a+'" title="'+ads[i].name+'" target="_blank">'+ads[i].img+'</a><p class="item-price"><span class="price">'+ads[i].price+'</span></p></div>');
                }
                newRow.appendChild(newCell);
                i++;
            }
            thebody.appendChild(newRow);
        }
        var thetable = document.createElement("table");

        var cssAttr = '';
        if(ads){
            if(ads.length > 0){
                cssAttr += 'background: #fff  no-repeat bottom right; padding-bottom: 20px; border:1px solid #000;';
            }
        }
        thetable.setAttribute("style",'width: '+AFF.config.width_to_generate+'px; '+cssAttr);
        thetable.appendChild(thebody);
        var elementHolder = document.getElementById('ad_holder_'+AFF.config.campaign_id);
        if(elementHolder != undefined)
            elementHolder.appendChild(thetable);
        this.status=1;
    }
};
document.write('<div id="ad_container_'+AFF.config.campaign_id+'"><div id="ad_holder_'+AFF.config.campaign_id+'"></div></div>');
Bilna.print();
EOT;
        $js = str_replace( array('__CONFIG__','__ADS__'), array(json_encode($config), json_encode($x)), $s);

        $this->getResponse()->setBody($block->toHtml());
        //$this->getResponse()->setBody($js);
//print_r($js);
        /*$response->setData('result', $p);

        $response->setMessages($messages);
        $this->getResponse()->setBody($response->toJson());*/
        return;
    }

    public function getPriceEachProduct()
    {

    }

}