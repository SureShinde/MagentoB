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
 * @package    AW_Onsale
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

    /**
 * On Sale Data Helper
 */
class AW_Onsale_Helper_Data extends Mage_Core_Helper_Abstract 
{
     /**
     * Current product instance
     * @var Mage_Catalog_Model_Product
     */
	protected $_product;

    /**
     * Cached collection
     * @var Mage_Catalog_Model_Eav_Resource_Product_Collection
     */
    protected $_collection;

    /**
     * Category id
     * @var int|string
     */
    protected $_categoryId;

    /**
     * Default attributes for select with product
     * @var array
     */
    protected $_attributesToSelect = array(
                    'aw_os_product_display',
                    'aw_os_product_image',
                    'aw_os_product_text',
                    'aw_os_product_position',
                    'aw_os_category_display',
                    'aw_os_category_image',
                    'aw_os_category_text',
                    'aw_os_category_position' );

    
    /**
     * Cahce of Image Urls Existanse
     * @var array
     */
    protected $_urlExistsCache = array();
    protected $_imageSizeCache = array();
    /**
     * Retrives Product label html
     * (Deprecated from 2.0 Saved for backfunctionality)
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
	public function getProductOnsaleLabelHtml($product) 
    {
        return $this->getProductLabelHtml( $product );
    }
        
    /**
     * Retrives Category label html
     * (Deprecated from 2.0 Saved for backfunctionality)
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
	public function getCategoryOnsaleLabelHtml($product) 
    {
        return $this->getCategoryLabelHtml( $product );
	}

    /**
     * Retrives Product label html
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getProductLabelHtml($product)
    {

        /* product attributes or rules */
        $label = Mage::getModel('onsale/label')
                ->getForProductPage($product, $this->getWebsiteId(), $this->getCustomerGroupId());

        if ($label->getData()) {
            return Mage::getSingleton('core/layout')
                            ->createBlock('onsale/product_view_label')
                            ->setTemplate('onsale/product/label.phtml')
                            ->setProduct($product)
                            ->setLabel($label)
                            ->toHtml();
        }

        return Mage::getSingleton('core/layout')
                        ->createBlock('onsale/product_label')
                        ->setTemplate('onsale/product/label.phtml')
                        ->setProductFlag()
                        ->setProduct($product)
                        ->toHtml();
    }

    /**
     * Retrives Category label html
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getCategoryLabelHtml($product)
    {
        /* product attributes or rules */

        $label = Mage::getModel('onsale/label')
                ->getForCategoryPage(
                $product, $this->getWebsiteId(), $this->getCustomerGroupId(), $this->getCurrentProductIds()
        );

        if ($label->getData()) {
            return Mage::getSingleton('core/layout')
                            ->createBlock('onsale/product_list_label')
                            ->setTemplate('onsale/category/label.phtml')
                            ->setProduct($product)
                            ->setLabel($label)
                            ->toHtml();
        }

        return Mage::getSingleton('core/layout')
                        ->createBlock('onsale/product_label')
                        ->setTemplate('onsale/category/label.phtml')
                        ->setCategoryFlag()
                        ->setProduct($product)
                        ->toHtml();
    }

    /**
     * Set up category id
     * @param int|string $categoryId
     * @return AW_Onsale_Helper_Data
     */
    public function setCategoryId( $categoryId )
    {
        $this->_categoryId = $categoryId;
        return $this;
    }

    /**
     * Retrives product collection for this category
     * @return Mage_Catalog_Model_Eav_Resource_Product_Collection
     */
    public function getCollection()
    {
        if ( ! $this->_collection )
        {
            $collection = Mage::getModel('catalog/product')->getLoadedProductCollection();
            if( ! $collection )
            $this->_collection = Mage::getModel('catalog/product')
                                 ->getCollection()
                                 ->setStoreId( Mage::app()->getStore()->getId() )
                                 ->addCategoryFilter( Mage::getSingleton('catalog/category')->setId( $this->_categoryId ) )
                                 ->addAttributeToSelect( Mage::getSingleton('catalog/config')->getProductAttributes() );

            else
            $this->_collection = $collection
                                 ->setStoreId( Mage::app()->getStore()->getId() )
                                 ->addCategoryFilter( Mage::getSingleton('catalog/category')->setId( $this->_categoryId ) )
                                 ->addAttributeToSelect( Mage::getSingleton('catalog/config')->getProductAttributes() );
                                 
            if ( count( $this->_attributesToSelect ) )
            {
                foreach ( $this->_attributesToSelect as $code )
                {
                    $this->_collection->addAttributeToSelect( $code );
                }
            }
        }
        return $this->_collection;
    }

    /**
     * Retrives product instance
     * @param int|string $id
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($id)
    {
        if(! $this->_product)
            return Mage::getModel('catalog/product')->load($id);
        else
            return $this->_product;

    }

    /**
     * Retrives configuration from product attributes
     * @param string $route
     * @param string $name
     * @param int|string $productId
     * @return mixed
     */
    public function confGetEavValue($route, $name, $product)
    {
        $old = Mage::registry('os_product');

        if (!isset($old['entity_id']) || ($old['entity_id'] !== $product->getId())) {
            /*@deprecated. Reason: big load time*/
            #$product = $this->getProduct($product->getId());
            Mage::unregister('os_product');
            Mage::register('os_product', $product->getData());
        }
        $product = Mage::registry('os_product');

        $name = 'aw_os_' . $route . '_' . $name;
        
        $ttr = isset($product[$name]) ? $product[$name] : '';

        return $ttr;
    }

    /**
     * Retrives configuration from all labels
     * @param string $type
     * @param string $route
     * @param string $name
     * @param object $product
     * @return mixed
     */
    public function confGetCustomValue( $route, $type, $name, $product, $useSystemValue = false )
    {
        if ( $route && $type && $name )
        {
            if ( $type === AW_Onsale_Block_Product_Label::TYPE_CUSTOM )
            {
                $value = $this->confGetEavValue( $route, $name, $product );
                if (!$useSystemValue || $value) {
                    return $value;
                }
                else {
                    return Mage::getStoreConfig("onsale/".$route."_".AW_Onsale_Block_Product_Label::TYPE_ONSALE."_label/".$name);
                }
            }
            else
            {
                return Mage::getStoreConfig("onsale/".$route."_".$type."_label/".$name);
            }
            return Mage::getStoreConfig("onsale/".$route."_".$type."_label/".$name);
        }
        else
        {
            return null;
        }
    }

    /**
     * Retrives product attribute
     * @param string $code
     * @param int|string $productId
     * @return mixed
     */
    public function getAttribute( $code, $product )
    {
        if (($attributes = $product->getAttributes()) && count($attributes)){
            foreach($attributes as $attribute){
                if ($attribute->getAttributeCode() == $code){
                    if ($attribute->getFrontendInput() == 'price') {
                        return Mage::app()->getStore()->convertPrice($attribute->getFrontend()->getValue($product), true);
                    } else {
                        $value = $attribute->getFrontend()->getValue($product);
                        if (is_string($value)){
                            return $value;
                        } else {
                            return null;
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * Retrives stock attribute
     * @param string $code
     * @param int|string $productId
     * @return mixed
     */
    public function getStockAttribute( $code, $product )
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        if ( $stockItem ){
            $this->_inStock = intval($stockItem->getQty());
            return $stockItem->getData($code);
        } else {
            return null;
        }
    }

    /**
     * Retrives custom product attribute
     * @param string $code
     * @param int|string $productId
     * @return mixed
     */
    public function getCustomAttributeValue( $attribute, $product )
    {
        $this->getCollection()->addAttributeToSelect( $attribute )->_loadAttributes()   ;
        return $this->getAttribute( $attribute, $product );
    }
    
    public function getCustomerGroupId()
    {
        $groupId = 0;
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        return $groupId;
    }

    public function getWebsiteId()
    {
        return Mage::app()->getStore()->getWebsite()->getId();
    }

    public function getCurrentProductIds()
    {
        $layer = Mage::registry('current_layer');
        if (!$layer) {
            $layer = Mage::getSingleton('catalog/layer');
        }

        return $layer->getProductCollection()->getAllIds();
    }
    
    public function urlExists($url = null)
    {
        if ($url == null) {
            return false;
        }
        $key = md5($url);
        if (isset($this->_urlExistsCache[$key])) {
            return $this->_urlExistsCache[$key];
        }

        $existsCache = $this->_getUrlExistsCache($key);
        if ($existsCache) {
            $this->_urlExistsCache[$key] = $existsCache;
            return $this->_urlExistsCache[$key];
        }
        
        # Version 4.x supported
        $handle = curl_init(str_replace('://www.', '://', $url));
        if (false === $handle) {
            return false;
        }
        try {
            Varien_Profiler::start('aw::onsale::label::url_exists');
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_FAILONERROR, true); // this works
            curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15")); // request as if Firefox
            curl_setopt($handle, CURLOPT_NOBODY, true);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
            $connectable = @curl_exec($handle);
            curl_close($handle);
            Varien_Profiler::stop('aw::onsale::label::url_exists');
        } catch (Exception $e) {
            return false;
        }
        $this->_urlExistsCache[$key] = $connectable;
        $this->_setUrlExistsCache($key, $connectable);
        
        return $this->_urlExistsCache[$key];
    }
    
    protected function _getUrlExistsCache($key)
    {
        $cache = Mage::app()->getCache();
        $size = $cache->load("aw_onsale_url_" . $key);
        return unserialize($size);
    }

    protected function _setUrlExistsCache($key, $size)
    {
        $cache = Mage::app()->getCache();
        $cache->save(serialize($size), "aw_onsale_url_" . $key, array("aw_onsale_url"), 3600);
    }

    public function getImageSize($url)
    {
        $default = array(
            AW_Onsale_Model_Label::DEFAULT_IMAGE_WIDTH,
            AW_Onsale_Model_Label::DEFAULT_IMAGE_HEIGHT,
        );

        if ($this->urlExists($url)) {
            $key = md5($url);
            if (!isset($this->_imageSizeCache[$key])) {
                
                $size = $this->_getImageSizeCache($key);
                if ($size) {
                    $this->_imageSizeCache[$key] = $size;
                    return $this->_imageSizeCache[$key];
                }

                try {

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    $resource = imagecreatefromstring($data);
                    $_w = imagesx($resource);
                    $_h = imagesy($resource);
                    imagedestroy($resource);

                    $this->_imageSizeCache[$key] = array($_w, $_h);
                    $this->_setImageSizeCache($key, array($_w, $_h));
                } catch (Exception $exc) {
                    return $default;
                }
            }
            return $this->_imageSizeCache[$key];
        }
        return $default;
    }

    protected function _getImageSizeCache($key)
    {
        $cache = Mage::app()->getCache();
        $size = $cache->load("aw_onsale_size_" . $key);
        return unserialize($size);
    }

    protected function _setImageSizeCache($key, $size)
    {
        $cache = Mage::app()->getCache();
        $cache->save(serialize($size), "aw_onsale_size_" . $key, array("aw_onsale_size"), 3600);
    }
    
    /**
     * Retrives full path to image file
     * @param string $imagePath
     * @return string
     */
    public function getImagePathUrl($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        if (strpos($imagePath, "http://") !== false || strpos($imagePath, "https://") !== false) {
            $url = $imagePath;
        } else {
            $imagePath = str_replace(BP, '', $imagePath);
            if ($imagePath[0] == '/') {
                $imagePath = substr($imagePath, 1, strlen($imagePath));
            }
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $imagePath;
        }
        return $url;
    }

}