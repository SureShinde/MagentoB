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
    protected $_storeId = 1;
    
    protected function _retrieve()
    {
        //passing this params from api url
        //campaign_id=2&affiliate_id=2558&traffic_source_generate=&width_to_generate=120x600&category_to_generate=2&store_id=1&category_option_to_generate=null
        
    	$campaignId = $this->getRequest()->getParam('campaign_id');
        $affiliateId = $this->getRequest()->getParam('affiliate_id');
        $trafficSourceGenerate = $this->getRequest()->getParam('traffic_source_generate');
        $widthToGenerate = $this->getRequest()->getParam('width_to_generate');
        $categoryToGenerate = $this->getRequest()->getParam('category_to_generate');
        $storeId = $this->getRequest()->getParam('store_id');
        $categoryOptionToGenerate = $this->getRequest()->getParam('category_option_to_generate');
        
        $params = array(
            'campaign_id' => $campaignId, 
            'affiliate_id' => $affiliateId, 
            'traffic_source_generate' => $trafficSourceGenerate, 
            'width_to_generate' => $widthToGenerate, 
            'category_to_generate' => $categoryToGenerate, 
            'store_id' => $storeId, 
            'category_option_to_generate' => $categoryOptionToGenerate
        );
        
        //$respon = $this->_generateHtmlLink($params);
        
        return array(
            'html' => $this->_productsScript($params)
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
        
        return file_get_contents($url);
    }
    
    private function _productsScript($data = array())
    {
        $storeId = $data['store_id'];
        $this->_storeId = $storeId;
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
                $collection = $this->_getBestProductCollection($data['campaign_id'], $limit);

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
                //problem with output query
                /**
                 * $collection = $this->_getNewProductCollection($data['category_to_generate'], $limit);
                
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
                 * 
                 */
                
                $collection = $this->_getNewProductCollection($data['category_to_generate'], $limit);
                
                if(!empty($collection))
                {
                    foreach ($collection as $_product)
                    {
                        $baseUrl = trim($_product['url_key']);
                        $params = array(
                            AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $data['campaign_id'], //$campaign->getId(),
                            AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $data['affiliate_id'], //$affiliate->getId(),
                            AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $trafficId
                        );
                        $resultUrl = Mage::helper('awaffiliate/affiliate')->generateAffiliateLink($baseUrl, $params);

                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135).'" width="44" height="44" /></a>';
                        $p['id']   = $_product['entity_id'];
                        $p['a']    = $resultUrl;
                        $p['img']  = '<img src="'.str_replace('/cache/0/', '/cache/'.$this->_storeId.'/', $_product['small_image']).'" width="'.$width.'" height="'.$height.'" style="z-index: 200" />';//Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135);
                        $p['name'] = strip_tags($_product['name']);
                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="http://demo.mage-world.com/1501/media/catalog/product/cache/1/thumbnail/44x44/9df78eab33525d08d6e5fb8d27136e95/h/t/htc-touch-diamond.jpg" width="44" height="44" /></a>';
                        $p['price'] = $this->_getPriceEachProduct($_product['price']); //$_product->getPrice();//$helper->getPriceHtml($_product->getPrice()); //$this->getLayout()->getBlock('product.info')->getPriceHtml($_product, true);/   
                        $x[] = $p;
                    }
                    $messages[] = 'success';
                }else{

                    $messages[] = 'fail';
                }
                break;
            
            case 3: /*case by category products*/
                $collection = $this->_getCategoryProductCollection($data['category_option_to_generate'], $storeId, $limit);
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
                        $p['img']  = '<img src="'.Mage::helper('catalog/image')->init($_product['small_image'], 'small_image')->resize(135).'" width="'.$width.'" height="'.$height.'" style="z-index: 200" />';//Mage::helper('catalog/image')->init($_product, 'small_image')->resize(135);
                        $p['name'] = $helper->stripTags($_product->getName());
                        //$p[$_product->getId()]['a']    = '<a href="'. $resultUrl.'" class="product-image"><img src="http://demo.mage-world.com/1501/media/catalog/product/cache/1/thumbnail/44x44/9df78eab33525d08d6e5fb8d27136e95/h/t/htc-touch-diamond.jpg" width="44" height="44" /></a>';
                        $p['price'] = $this->_getPriceEachProduct($_product); //$_product->getPrice();//$helper->getPriceHtml($_product->getPrice()); //$this->getLayout()->getBlock('product.info')->getPriceHtml($_product, true);/   
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
    
    private function _getNewProductCollection($categoryToGenerate, $limit)
    {
        //problem with output query
        /**
         * SELECT `e`.*, `cat_index`.`position` AS `cat_index_position`, `price_index`.`price`, `price_index`.`tax_class_id`, `price_index`.`final_price`, IF(price_index.tier_price IS NOT NULL, LEAST(price_index.min_price, price_index.tier_price), price_index.min_price) AS `minimal_price`, `price_index`.`min_price`, `price_index`.`max_price`, `price_index`.`tier_price`, `at_news_from_date`.`value` AS `news_from_date` FROM `catalog_product_entity` AS `e`
            INNER JOIN `catalog_category_product_index` AS `cat_index` ON cat_index.product_id=e.entity_id AND cat_index.store_id=0 AND cat_index.visibility IN(3, 2, 4) AND cat_index.category_id = '0'
            INNER JOIN `catalog_product_index_price` AS `price_index` ON price_index.entity_id = e.entity_id AND price_index.website_id = '0' AND price_index.customer_group_id = 0
            INNER JOIN `catalog_product_entity_datetime` AS `at_news_from_date` ON (`at_news_from_date`.`entity_id` = `e`.`entity_id`) AND (`at_news_from_date`.`attribute_id` = '93') AND (`at_news_from_date`.`store_id` = 1) WHERE (at_news_from_date.value <= '2015-11-09 14:53:07') ORDER BY `at_news_from_date`.`value` desc LIMIT 6;
         */
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $visibleSiteId = implode(',', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());
        
        /*
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());
        $store = Mage::getModel('core/store')
                ->setId(1)
                ->setCode('default');
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter($store)
            ->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
            ->addAttributeToSort('news_from_date', 'desc');
        $collection->getSelect()->limit( $limit ); 
        
        //$collection->printLogQuery(true);
        */
        
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');  
        /**
	 * Retrieve table name
	 */
	$table = 'catalog_product_flat_'.$this->_storeId;
        $query = "SELECT 1 AS `status`, `e`.`entity_id`, `e`.`type_id`, `e`.`attribute_set_id`, "
                . "`cat_index`.`position` AS `cat_index_position`, `price_index`.`price`, "
                . "`price_index`.`tax_class_id`, `price_index`.`final_price`, "
                . "IF(price_index.tier_price IS NOT NULL, "
                . "LEAST(price_index.min_price, price_index.tier_price), price_index.min_price) "
                . "AS `minimal_price`, `price_index`.`min_price`, `price_index`.`max_price`, "
                . "`price_index`.`tier_price`, `e`.`name`, `e`.`short_description`, "
                . "`e`.`price`, `e`.`special_price`, `e`.`special_from_date`, "
                . "`e`.`special_to_date`, `e`.`small_image`, `e`.`thumbnail`, "
                . "`e`.`news_from_date`, `e`.`news_to_date`, `e`.`url_key`, "
                . "`e`.`required_options`, `e`.`image_label`, `e`.`small_image_label`, "
                . "`e`.`thumbnail_label`, `e`.`created_at`, `e`.`msrp_enabled`, "
                . "`e`.`msrp_display_actual_price_type`, `e`.`msrp`, `e`.`tax_class_id`, "
                . "`e`.`price_type`, `e`.`weight_type`, `e`.`price_view`, `e`.`shipment_type`, "
                . "`e`.`links_purchased_separately`, `e`.`links_exist`, "
                . "`e`.`smd_colorswatch_product_list`, `e`.`nipple_size`, `e`.`nipple_size_value`, "
                . "`e`.`volume_weight`, `e`.`aw_os_product_display`, `e`.`aw_os_product_position`, "
                . "`e`.`aw_os_product_image`, `e`.`aw_os_product_text`, `e`.`aw_os_category_display`, "
                . "`e`.`aw_os_category_position`, `e`.`aw_os_category_image`, "
                . "`e`.`aw_os_category_text`, `e`.`aw_os_product_image_path`, "
                . "`e`.`aw_os_category_image_path` "
                . "FROM `".$table."` AS `e` "
                . "INNER JOIN `catalog_category_product_index` AS `cat_index` "
                . "ON cat_index.product_id=e.entity_id AND cat_index.store_id='".$this->_storeId."' "
                . "AND cat_index.visibility "
                . "IN(".$visibleSiteId.") "
                . "AND cat_index.category_id = '".$categoryToGenerate."' "
                . "INNER JOIN `catalog_product_index_price` AS `price_index` "
                . "ON price_index.entity_id = e.entity_id AND price_index.website_id = '1' "
                . "AND price_index.customer_group_id = 0 "
                . "WHERE (e.news_from_date <= '".$todayDate."') "
                . "ORDER BY `e`.`news_from_date` desc "
                . "LIMIT ".$limit.";";
        
        return $read->fetchAll($query);
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addProductAttributesAndPrices(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addUrlRewrite();
    }

    private function _getBestProductCollection($campaign_id, $limit)
    {
        //$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getResourceModel('catalog/product_collection');
        //$collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());

        $collection->getSelect()
            ->joinInner(
            array( 'prod_index' => Mage::getSingleton('core/resource')->getTableName('awaffiliate/products') ),
            "prod_index.product_id = e.entity_id AND prod_index.campaign_id='".$campaign_id."'"
        );

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter();
/*            ->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
            ->addAttributeToSort('news_from_date', 'desc');*/
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));    
        $collection->getSelect()->limit( $limit );

        return $collection;
   }

   private function _getCategoryProductCollection($category_id='2', $storeId, $limit)
   {
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getResourceModel('catalog/product_collection');
        //$collection->setVisibility(array_push(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), array('category_id' => $category_id))); 
        $collection->getSelect()
            ->joinInner(
            array( 'cat_index' => Mage::getSingleton('core/resource')->getTableName('catalog/category_product_index') ),
            "cat_index.product_id = e.entity_id AND cat_index.category_id= '".$category_id."' AND cat_index.visibility IN (".implode(",", Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()).") AND cat_index.store_id='".$storeId."'",
            array( 
                'category_id'        => 'cat_index.category_id'
            )
        );

        $collection = $this->_addProductAttributesAndPrices($collection);
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
        $collection->getSelect()->limit( $limit ); 

        return $collection;
    }

    private function _getPriceEachProduct($_product)
    {
        $this->helper = new Mage_Core_Helper_Data();
        
        /** @var $_coreHelper Mage_Core_Helper_Data */
        $_coreHelper        = $this->helper('core');
        /** @var $_weeeHelper Mage_Weee_Helper_Data */
        $_weeeHelper        = $this->helper('weee');
        /** @var $_taxHelper Mage_Tax_Helper_Data */
        $_taxHelper         = $this->helper('tax');

        //$_product           = $this->getProduct();
        $_id                = $_product['entity_id'];
        $_storeId           = $this->_storeId;
        $_website           = 1;

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
}