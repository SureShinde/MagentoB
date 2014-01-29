<?php
/**
 *
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    1.0.3
 * @copyright  Copyright (c) 2014 (http://www.bilna.com)
 * @license    
 */


class AW_Affiliate_Block_Campaign_Products extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $_template = 'aw_affiliate/campaign/products/generate_link.phtml';
        $this->setTemplate($_template);
        return $this;
    }

    public function getUrlToRequest()
    {
        $campaignId = Mage::registry('current_campaign')->getId();
        $affiliateId = Mage::registry('current_affiliate')->getId();
        $params = array(
            'campaign_id' => $campaignId,
            'affiliate_id' => $affiliateId
        );
        return Mage::getUrl('awaffiliate/customer_affiliate/generateLink', $params);
    }

    public function getCampaignId()
    {
        return Mage::registry('current_campaign')->getId();
    }

    public function getAffiliateId()
    {
        return Mage::registry('current_affiliate')->getId();
    }

    public function getUrlProductsToRequest()
    {
        $campaignId = Mage::registry('current_campaign')->getId();
        $affiliateId = Mage::registry('current_affiliate')->getId();
        $params = array(
            'campaign_id' => $campaignId,
            'affiliate_id' => $affiliateId
        );
        return Mage::getUrl('awaffiliate/customer_affiliate/products', $params);
        //return Mage::getUrl('awaffiliate/customer_affiliate/products');
    }

    public function getUrlProductsScriptRequest($titleToGenerate, $numOfProductsToGenerate)
    {
        $campaignId = Mage::registry('current_campaign')->getId();
        $affiliateId = Mage::registry('current_affiliate')->getId();
        $params = array(
            'campaign_id' => $campaignId,
            'affiliate_id' => $affiliateId,
            'title_to_generate' => $titleToGenerate,
            'num_of_products_to_generate' => $numOfProductsToGenerate
        );
        return Mage::getUrl('awaffiliate/customer_affiliate/productsScript', $params);
        //return Mage::getUrl('awaffiliate/customer_affiliate/products');
    }

    public function getInputTitleField()
    {
        $campaignUrl = Mage::registry('current_campaign')->getUrl();

        $input = new Varien_Data_Form_Element_Text(array(
            'label' => $this->__('Title'),
            'html_id' => 'title-to-generate',
            'name' => 'title_to_generate',
            'no_span' => true,
            'required' => true
        ));
        
        $input->setData('value', $this->__('You might also like') );
        
        $input->setForm(new Varien_Object());
        return $input->getHtml();
    }

    public function getTrafficSourceLinkField()
    {
        $input = new Varien_Data_Form_Element_Text(array(
            'label' => $this->__('Traffic Source'),
            'html_id' => 'traffic-source-generate',
            'name' => 'traffic_source_generate',
            'no_span' => true,
            'after_element_html' => $this->__('Custom value to group data in reports.')
        ));

        $input->setForm(new Varien_Object());
        return $input->getHtml();
    }

    public function getWidthCampaignField()
    {
        $input = new Varien_Data_Form_Element_Select(array(
            'label' => $this->__('Select Dimension'),
            'values'     => array(
                '299x250' => $this->__('Medium Rectangel (299 x 250)'),
                '728x90' => $this->__('Leaderboard (728 x 90)'),
                '468x60' => $this->__('Full Banner (468 x 60)'),
                '320x50' => $this->__('Mobile Banner (320 x 50)'),
                '160x600' => $this->__('Wide Skycraper (160 x 600)'),
                '120x600' => $this->__('Skycraper (120 x 600)'),
                '299x600' => $this->__('Hafl Page (299 x 600)')
            ),
            'html_id'   => 'width-to-generate',
            'name'      => 'width_to_generate',
            'no_span'   => true
        ));

        $input->setData('value', '728x90' );

        $input->setForm(new Varien_Object());
        return $input->getHtml();
    }

    public function getNumberOfProductsField()
    {
        $input = new Varien_Data_Form_Element_Text(array(
            'label'     => $this->__('Number of Products'),
            'html_id'   => 'numofproducts-to-generate',
            'name'      => 'numofproducts_to_generate',
            'no_span'   => true
        ));

        $input->setData('value', '6' );

        $input->setForm(new Varien_Object());
        return $input->getHtml();
    }

    public function getCategoryOfProductsField()
    {
        $input = new Varien_Data_Form_Element_Radios(array(
            'values'     => array(
                array('label'=> $this->__('Select by bestseller'), 'value' => '1'),
                array('label'=> $this->__('Select by new products'), 'value' => '2'),
                array('label'=> $this->__('Select by category'), 'value' => '3')
            ),
            'html_id'   => 'category-to-generate',
            'name'      => 'category_to_generate',
            'no_span'   => true
        ));

        $input->setData('value', '2' );

        $input->setForm(new Varien_Object());
        return $input->getHtml();
    }

    public function getCategoriesField()
    {
        $input = new Varien_Data_Form_Element_Select(array(
            'label' => $this->__('Select Products Category'),
            'values'     => $this->__getCategories(),
            'html_id'   => 'categories-to-generate',
            'name'      => 'categories_to_generate',
            'no_span'   => true
        ));

        //$input->setData('value', '2' );

        $input->setForm(new Varien_Object());
        return $input->getHtml();
    }

    public function getResultLinkField()
    {
        $textarea = new Varien_Data_Form_Element_Textarea(array(
            'label' => $this->__('Tracking Link'),
            'html_id' => 'result',
            'name' => 'result',
            'no_span' => true,
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('result'))) {
            $textarea->setData('value', $_defaultValue);
        }
        $textarea->setForm(new Varien_Object());
        return $textarea->getHtml();
    }

    private function __getDefaultValue($key)
    {
        $_session = Mage::getSingleton('customer/session');
        $formData = $_session->getGenerateLinkFormData();
        return isset($formData[$key]) ? $formData[$key] : null;
    }

    /*start added for get image for banner*/
    public function getImageField()
    {
        $campaignName = Mage::registry('current_campaign')->getName();
        return $campaignName;
    }

    private function __getCategories()
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId(1)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active'); 
        $collection->getSelect()
            ->joinInner(
                array( 'awaffiliate_cat' => Mage::getSingleton('core/resource')->getTableName('awaffiliate/categories') ),
                "main_table.entity_id = awaffiliate_cat.category_id",
                array(
                    "category_id" => "awaffiliate_cat.category_id"
                )
            );

        $values=array();

        foreach ($collection as $row){
           $values[] = array(
                'label' => Mage::helper('reports')->__($row['name']),
                'value' => $row['entity_id']
            );
        }

        return $values;
    }


    public function productsScriptAction()
    {
        $data = $this->getRequest()->getParams();
        $storeId = $data['store_id'];
        /*later we can change to DB, but we need it ?????*/
        switch ($data['width_to_generate']) {
            case '299x250':
                $limit = 6;
                $width = 89;
                $height = 89;
                break;
            case '728x90':
                $limit = 7;
                $width = 72;
                $height = 72;
                break;
            case '468x60':
                $limit = 6;
                $width = 51;
                $height = 51;
                break;
            case '320x50':
                $limit = 4;
                $width = 42;
                $height = 42;
                break;
            case '160x600':
                $limit = 6;
                $width = 74;
                $height = 74;
                break;
            case '120x600':
                $limit = 3;
                $width = 112;
                $height = 126;
                break;
            case '299x600':
                $limit = 12;
                $width = 114;
                $height = 78;
                break;
            default:
                $limit = 7;
                $width = 72;
                $height = 72;
                break;
        }

        $response = new Varien_Object();
        $response->setError(0);

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

        $helper = Mage::helper('adminhtml');
        switch ($data['category_to_generate']) {
            case 1: /*case for best products*/
                $_block = $this->getLayout()->getBlockSingleton('awaffiliate/campaign_product_list');

                $collection = $_block->getBestProductCollection($data['campaign_id'], $limit);

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
                        $p['img']  = '<img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" width="'.$width.'" height="'.$height.'" style="z-index: 200" />';//Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135);
                        $p['name'] = $helper->stripTags($_product->getName());
                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="http://demo.mage-world.com/1501/media/catalog/product/cache/1/thumbnail/44x44/9df78eab33525d08d6e5fb8d27136e95/h/t/htc-touch-diamond.jpg" width="44" height="44" /></a>';
                        $p['price'] = $this->getPriceEachProduct($_product); //$_product->getPrice();//$helper->getPriceHtml($_product->getPrice()); //$this->getLayout()->getBlock('product.info')->getPriceHtml($_product, true);/   
                        $x[] = $p;
                    }
                    $messages[] = 'success';
                }else{

                    $messages[] = 'fail';
                }
                break;
            
            case 2: /*case for new products*/
                $_block = $this->getLayout()->getBlockSingleton('awaffiliate/campaign_product_list');

                $collection = $_block->getNewProductCollection($limit);

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
                        $p['img']  = '<img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" width="'.$width.'" height="'.$height.'" style="z-index: 200" />';//Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135);
                        $p['name'] = $helper->stripTags($_product->getName());
                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="http://demo.mage-world.com/1501/media/catalog/product/cache/1/thumbnail/44x44/9df78eab33525d08d6e5fb8d27136e95/h/t/htc-touch-diamond.jpg" width="44" height="44" /></a>';
                        $p['price'] = $this->getPriceEachProduct($_product); //$_product->getPrice();//$helper->getPriceHtml($_product->getPrice()); //$this->getLayout()->getBlock('product.info')->getPriceHtml($_product, true);/   
                        $x[] = $p;
                    }
                    $messages[] = 'success';
                }else{

                    $messages[] = 'fail';
                }
                break;
            
            case 3: /*case by category products*/
                $_block = $this->getLayout()->getBlockSingleton('awaffiliate/campaign_product_list');

                $collection = $_block->getCategoryProductCollection($data['category_option_to_generate'], $storeId, $limit);

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
                        $p['img']  = '<img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" width="'.$width.'" height="'.$height.'" style="z-index: 200" />';//Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135);
                        $p['name'] = $helper->stripTags($_product->getName());
                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="http://demo.mage-world.com/1501/media/catalog/product/cache/1/thumbnail/44x44/9df78eab33525d08d6e5fb8d27136e95/h/t/htc-touch-diamond.jpg" width="44" height="44" /></a>';
                        $p['price'] = $this->getPriceEachProduct($_product); //$_product->getPrice();//$helper->getPriceHtml($_product->getPrice()); //$this->getLayout()->getBlock('product.info')->getPriceHtml($_product, true);/   
                        $x[] = $p;
                    }
                    $messages[] = 'success';
                }else{

                    $messages[] = 'fail';
                }
                break;
            default:
                # code...
                break;
        }

        return $x;
    }

    public function getPriceEachProduct($_product)
    {
        /** @var $_coreHelper Mage_Core_Helper_Data */
        $_coreHelper        = $this->helper('core');
        /** @var $_weeeHelper Mage_Weee_Helper_Data */
        $_weeeHelper        = $this->helper('weee');
        /** @var $_taxHelper Mage_Tax_Helper_Data */
        $_taxHelper         = $this->helper('tax');

        //$_product           = $this->getProduct();
        $_id                = $_product->getId();
        $_storeId           = $_product->getStoreId();
        $_website           = Mage::app()->getStore($_storeId)->getWebsite();

        $_weeeSeparator     = '';
        $_simplePricesTax   = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
        $_minimalPriceValue = $_product->getMinimalPrice();
        $_minimalPrice      = $_taxHelper->getPrice($_product, $_minimalPriceValue, $_simplePricesTax);

        $_exclTax = $_taxHelper->getPrice($_product, $_minimalPriceValue, $includingTax = null);
        $_inclTax = $_taxHelper->getPrice($_product, $_minimalPriceValue, $includingTax = true);
        
        $_weeeTaxAmount = $_weeeHelper->getAmount($_product, null, null, $_website);
        
        if ($_weeeHelper->typeOfDisplay($_product, array(1,2,4))):
            $_weeeTaxAmount = $_weeeHelper->getAmount($_product, null, null, $_website);
            $_weeeTaxAttributes = $_weeeHelper->getProductWeeeAttributesForRenderer($_product, null, null, $_website);
        endif;

        $_price = $_taxHelper->getPrice($_product, $_product->getPrice());
        $_regularPrice = $_taxHelper->getPrice($_product, $_product->getPrice(), $_simplePricesTax);
        $_finalPrice = $_taxHelper->getPrice($_product, $_product->getFinalPrice());
        /*$_finalPriceInclTax = $_taxHelper->getPrice($_product, $_product->getFinalPrice(), true);
        $_weeeDisplayType = $_weeeHelper->getPriceDisplayType();

        if ($_finalPrice == $_price):
            if ($_taxHelper->displayBothPrices()):
                if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)):
                    $price_excluding_tax = $_coreHelper->currencyByStore($_price+$_weeeTaxAmount, $_storeId, true, false);

                endif;
            endif;
        endif;*/

        $old_price = $_coreHelper->currencyByStore($_regularPrice, $_storeId, true, false);
        $special_price = $_coreHelper->currencyByStore($_finalPrice, $_storeId, true, false);

        return array(
            'old_price'     => $old_price,
            'special_price' => $special_price
        );
    }

    /*end added for get image for banner*/
}
