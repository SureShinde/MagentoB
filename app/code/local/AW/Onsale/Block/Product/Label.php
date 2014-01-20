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
 * On Sale Label Class
 */
class AW_Onsale_Block_Product_Label extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Path to default image file name
     */
    const DEFAULT_IMAGE_ONSALE_PRODUCT = 'onsale.product.default.bg.png';

    /**
     * Path to default image file name
     */
    const DEFAULT_IMAGE_ONSALE_CATEGORY = 'onsale.category.default.bg.png';

    /**
     * Path to default image file name
     */
    const DEFAULT_IMAGE_NEW_PRODUCT = 'onsale.product.default.bg.png';

    /**
     * Path to default image file name
     */
    const DEFAULT_IMAGE_NEW_CATEGORY = 'onsale.category.default.bg.png';

    /**
     * Path to default image file name
     */
    const DEFAULT_IMAGE_CUSTOM_PRODUCT = 'onsale.product.default.bg.png';

    /**
     * Path to default image file name
     */
    const DEFAULT_IMAGE_CUSTOM_CATEGORY = 'onsale.category.default.bg.png';

    /**
     * No route flag
     */
    const NONE_ROUTE = 'none';

    /**
     * Product page flag
     */
    const PRODUCT_ROUTE = 'product';

    /**
     * Category page flag
     */
    const CATEGORY_ROUTE = 'category';

    /**
     * Sales Label flag
     */
    const TYPE_ONSALE = 'onsale';

    /**
     * New Product Label flag
     */
    const TYPE_NEW = 'new';

    /**
     * Custom Label flag
     */
    const TYPE_CUSTOM = 'custom';

    /**
     * Current product instance
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Discount amount value
     * @var string
     */
    protected $_discountAmount = '';

    /**
     * Regular price value
     * @var string
     */
    protected $_regularPrice = '';

    /**
     * Special price value
     * @var string
     */
    protected $_specialPrice = '';

    /**
     * Saved amount value
     * @var string
     */
    protected $_saveAmount = '';

    /**
     * Product added [x] days ago. X - is value
     * @var string
     */
    protected $_daysAgo = '';

    /**
     * Product added [x] hours ago. X - is value
     * @var string
     */
    protected $_hoursAgo = '';

    /**
     * Product sku value
     * @var string
     */
    protected $_productSku = '';

    /**
     * Count of product in stock
     * @var string
     */
    protected $_inStock = '';

    /**
     * Sales mutex flag
     * @var boolean
     */
    protected $_onSale = false;

    /**
     * Custom label mutex flag
     * @var boolean
     */
    protected $_custom = false;

    /**
     * New Product mutex flag
     * @var boolean
     */
    protected $_new = false;

    /**
     * Current rendering route
     * @var string
     */
    protected $_placeRoute = AW_Onsale_Block_Product_Label::NONE_ROUTE;

    /**
     * Current rendering type
     * @var string
     */
    protected $_type = AW_Onsale_Block_Product_Label::NONE_ROUTE;

    /**
     * File name
     * @var string
     */
    protected $_imageFile;

    /**
     * File URL
     * @var string
     */
    protected $_imageFileUrl;

    /**
     * Indicate showing of category IE Fixes
     * @var boolean
     */
    protected $_showOnceFlag = true;

    /**
     * Retrives Show Once Flag value
     * @return boolean
     */
    public function getShowOnceFlag()
    {
        return $this->_showOnceFlag;
    }

    /**
     * Reset Show Once Flaf
     * @return AW_Onsale_Block_Product_Label
     */
    public function resetShowOnceFlag()
    {
        $this->_showOnceFlag = false;
        return $this;
    }

    /**
     * Retrives sales flag
     * @return boolean
     */
    public function isOnSale()
    {
        return $this->_onSale;
    }

    /**
     * Retrives new product flag
     * @return boolean
     */
    public function isNew()
    {
        return $this->_new;
    }

    /**
     * Retrives custom label flag
     * @return boolean
     */
    public function isCustom()
    {
        return $this->_custom;
    }

    /**
     * Retrives Product Id
     * @return string
     */
    public function getTableId()
    {
        return $this->_product->getId();
    }

    /**
     * Retrives formatted label text
     * @return string
     */
    public function getFormatedText()
    {
        $pattern = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'text', $this->_product);
        $pattern = $this->getFormatedCommonText($pattern);
        $pattern = $this->getFormatedCustomText($pattern);
        return $pattern;
    }

    /**
     * Make replacement for #CA:customer_attribute# pattern
     * @return string
     */
    public function getFormatedCustomText($pattern)
    {
        $text = $pattern;
        $reg = '/#CA:[a-z_0-9]{1,255}#/';
        preg_match_all($reg, $text, $out, PREG_PATTERN_ORDER);
        array_unique($results = $out[0]);
        foreach ($results as $cut)
        {
            $attribute = str_replace(array('CA:', '#'), array('', ''), $cut);
            $value = Mage::helper('onsale')->getCustomAttributeValue($attribute, $this->_product);
            $text = str_replace($cut, $value, $text);
        }
        return $text;
    }

    /**
     * Make replacement for predefined patterns
     * @return string
     */
    public function getFormatedCommonText($pattern)
    {
        return str_replace(
            array(
                '#NL#',
                '#ND#',
                '#NH#',
                '#SA#',
                '#DA#',
                '#SK#',
                '#ST#',
                '#RP#',
                '#SP#',
            ),
            array(
                '<br />',
                $this->_daysAgo,
                $this->_hoursAgo,
                $this->_saveAmount,
                $this->_discountAmount,
                $this->_productSku,
                $this->_inStock,
                $this->_regularPrice,
                $this->_specialPrice,
            ),
            $pattern
        );
    }

    /**
     * Retrives Product Instance
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Set up category flag
     * @return AW_Onsale_Block_Product_Label
     */
    public function setCategoryFlag()
    {
        $this->_placeRoute = AW_Onsale_Block_Product_Label::CATEGORY_ROUTE;
        return $this;
    }

    /**
     * Set up product flag
     * @return AW_Onsale_Block_Product_Label
     */
    public function setProductFlag()
    {
        $this->_placeRoute = AW_Onsale_Block_Product_Label::PRODUCT_ROUTE;
        return $this;
    }

    /**
     * Set target product for rendering of label
     * @return AW_Onsale_Block_Product_Label
     */
    public function setProduct($product)
    {
        # Fix #2111
        if (!$product->getCreatedAt()) {
            $product->setCreatedAt(Mage::helper('onsale')->getCustomAttributeValue('created_at', $product));
        }
        # End fix #2111

        $this->_product = $product;

        $this->_onSale = false;
        $this->_custom = false;
        $this->_new = false;

        //Set up category for helper collection load
        Mage::helper('onsale')->setCategoryId($product->getCategoryId());

        //Onsale price calculations
        if ($product->getTypeId() == 'bundle') {
            list($_minimalPrice, $_maximalPrice) = $product->getPriceModel()->getPrices($product);
            $this->_price = $_minimalPrice;
            $this->_specialPrice = $_minimalPrice;
            if (!is_null($product->getData('special_price')) && ($product->getData('special_price') < 100)) {
                $this->_regularPrice = ($this->_specialPrice / $product->getData('special_price')) * 100;
            } else {
                $this->_regularPrice = $this->_specialPrice;
            }
        } else {
            $this->_price = 0;
            $this->_regularPrice = $product->getPrice();
            $this->_specialPrice = $product->getFinalPrice();
        }

        if ($this->_specialPrice != $this->_regularPrice) {
            if ($this->_regularPrice > 0) {
                $this->_discountAmount = round((1 - $this->_specialPrice / $this->_regularPrice) * 100);
                $this->_saveAmount = $this->_regularPrice - $this->_specialPrice;

                if (Mage::helper('onsale')
                    ->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_ONSALE, 'display', $product) == 1
                ) {
                    $this->_onSale = true;
                }
                $threshold = Mage::helper('onsale')
                    ->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_ONSALE, 'threshold', $product);
                if ($threshold && $threshold > $this->_discountAmount) {
                    $this->_onSale = false;
                }
            }
        }

        //New calculations
        $days = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_NEW, 'days', $product);
        $isNew = $this->_isNewProduct($product->getCreatedAt(), $days);
        $isNativeNew = $this->_isNativeNewProduct($product);
        $overridesNativeNew = Mage::helper('onsale')
            ->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_NEW, 'overrides_native_new', $product);
        $nativeNewIsSettedUp = ($product->getNewsFromDate() || $product->getNewsToDate());
        if ($isNativeNew || $isNew) {
            if (
                ($nativeNewIsSettedUp && !$overridesNativeNew && $isNativeNew) ||
                (($isNew && $overridesNativeNew) || ($isNew && !$nativeNewIsSettedUp))
            ) {
                if (Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_NEW, 'display', $product) == 1) {
                    $this->_new = true;
                }
            }
        }

        if (Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_CUSTOM, 'display', $product)) {
            $this->_custom = true;
        }

        //Fill common of params
        $this->_inStock = (int)Mage::helper('onsale')->getStockAttribute('qty', $product);
        $this->_productSku = $product->getSku();
        $this->_daysAgo = $this->_getAbsDays($product->getCreatedAt());
        $this->_hoursAgo = $this->_getAbsHours($product->getCreatedAt());
        if ($this->_regularPrice) {
            $this->_regularPrice = strip_tags(Mage::app()->getStore()->convertPrice($this->_regularPrice, true));
        }
        if ($this->_specialPrice) {
            $this->_specialPrice = strip_tags(Mage::app()->getStore()->convertPrice($this->_specialPrice, true));
        }
        if ($this->_saveAmount) {
            $this->_saveAmount = strip_tags(Mage::app()->getStore()->convertPrice($this->_saveAmount, true));
        }
        if ($this->_discountAmount) {
            $this->_discountAmount = $this->_discountAmount . '%';
        }

        //Desides that we will show
        //Set up routes
        $overrides = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_NEW, 'overrides', $product);
        if ($this->_custom) {
            $this->_type = AW_Onsale_Block_Product_Label::TYPE_CUSTOM;
        } elseif ($this->_new && $overrides) {
            $this->_type = AW_Onsale_Block_Product_Label::TYPE_NEW;
        } elseif ($this->_onSale) {
            $this->_type = AW_Onsale_Block_Product_Label::TYPE_ONSALE;
        } else {
            $this->_type = AW_Onsale_Block_Product_Label::TYPE_NEW;
        }

        //Set Show params
        if ($this->isShow()) {
            if ($this->_custom) {
                $imageFile = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'image', $product, true);
                $imagePath = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'image_path', $product, true);
                if (!file_exists($imageFile)) {
                    $imageFile = 'uploaded' . DS . $imageFile;
                }
            } else {
                $imageFile = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'image', $product);
                $imagePath = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'image_path', $product);
            }
            $defaultImage = $this->_getDefaultImage();
            $this->setImageFile($imageFile, $defaultImage, $imagePath);
        }
        return $this;
    }

    /**
     * Retrives type of current label
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Retrives show flag
     * @return boolean
     */
    public function isShow()
    {
        return $this->_onSale || $this->_new || $this->_custom;
    }

    /**
     * Set up image for display on label
     * @param string $imageFile
     * @param string $defaultImage
     * @param string $imagePath
     * @return AW_Onsale_Block_Product_Label
     */
    public function setImageFile($imageFile, $defaultImage = AW_Onsale_Block_Product_Label::DEFAULT_IMAGE_ONSALE_PRODUCT, $imagePath = null)
    {
        $__imageFile = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'onsale' . DS . $imageFile;
        if (file_exists($__imageFile) and is_file($__imageFile)) {
            $this->_imageFile = $__imageFile;
            $this->_imageFileUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'onsale/' . $imageFile;
        } elseif ($this->_urlExists($this->_getImagePathUrl($imagePath))) {
            $this->_imageFile = $this->_getImagePathUrl($imagePath);
            $this->_imageFileUrl = $this->_getImagePathUrl($imagePath);
        } else {
            $this->_imageFile = Mage::getDesign()->getSkinBaseDir(array('_type' => 'skin', '_default' => false)) . DS . 'onsale' . DS . 'images' . DS . $defaultImage;
            if (!file_exists($this->_imageFile)) $this->_imageFile = Mage::getDesign()->getSkinBaseDir() . DS . 'onsale' . DS . 'images' . DS . $defaultImage;
            $this->_imageFileUrl = $this->getSkinUrl('onsale/images/' . $defaultImage);
        }
        return $this;
    }

    /**
     * Retrives full path to image file
     * @param string $imagePath
     * @return string
     */
    protected function _getImagePathUrl($imagePath)
    {
       return Mage::helper('onsale')->getImagePathUrl($imagePath);
    }

    /**
     * Check up file url
     * @param string $url
     * @return boolean
     */
    protected function _urlExists($url)
    {
        return Mage::helper('onsale')->urlExists($url);
    }

    /**
     * Retrives current image size html tag params
     * @return string
     */
    public function getImageSizeHtml()
    {
        if (file_exists($this->_imageFile)) {
            list($__w, $__h) = getimagesize($this->_imageFile);
        } elseif ($this->_urlExists($this->_imageFileUrl)) {
            try {
                list($__w, $__h) = getimagesize($this->_imageFileUrl);
            } catch (Exception $e) {
                list($__w, $__h) = array(80, 80);
            }
        } else {
            list($__w, $__h) = array(80, 80);
        }
        return 'width: ' . $__w . 'px; height: ' . $__h . 'px;';
    }

    /**
     * Retrives image url
     * @return string
     */
    public function getImageUrl()
    {
        return $this->_imageFileUrl;
    }

    /**
     * Check product for new for OnSale config
     * @param Timestamp $timestamp
     * @param int|string $dayCount
     * @return boolean
     */
    protected function _isNewProduct($timestamp, $dayCount)
    {
        $old_timestamp = strtotime($timestamp);
        $new_timestamp = strtotime(now()) - $dayCount * 86400;
        return ($old_timestamp > $new_timestamp);
    }

    /**
     * Check product for new for Native Magento Config
     * @param Timestamp $timestamp
     * @param int|string $dayCount
     * @return boolean
     */
    protected function _isNativeNewProduct($product)
    {
        if (($from_date = $product->getNewsFromDate()) && ($to_date = $product->getNewsToDate())) {
            $new_timestamp = strtotime(now());
            $from_timestamp = strtotime($from_date);
            $to_timestamp = strtotime($to_date);
            if (($new_timestamp >= $from_timestamp) && ($new_timestamp <= $to_timestamp)) {
                return true;
            } else {
                return false;
            }
        } elseif ($from_date = $product->getNewsFromDate() && !$product->getNewsToDate()) {
            $new_timestamp = strtotime(now());
            $from_timestamp = strtotime($from_date);
            if ($new_timestamp >= $from_timestamp) {
                return true;
            } else {
                return false;
            }
        } elseif (!$product->getNewsFromDate() && $to_date = $product->getNewsToDate()) {
            $new_timestamp = strtotime(now());
            $to_timestamp = strtotime($to_date);
            if ($new_timestamp <= $to_timestamp) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Retrives hours from current product created
     * @param Timestamp $timestamp
     * @return int
     */
    protected function _getAbsHours($timestamp)
    {
        $old_timestamp = strtotime($timestamp);
        $new_timestamp = strtotime(now());
        return abs(floor(($new_timestamp - $old_timestamp) / 3600));
    }

    /**
     * Retrives hours from current product created
     * @param Timestamp $timestamp
     * @return int
     */
    protected function _getAbsDays($timestamp)
    {
        $old_timestamp = strtotime($timestamp);
        $new_timestamp = strtotime(now());
        return abs(floor(($new_timestamp - $old_timestamp) / 86400));
    }

    /**
     * Retrives default image file name
     * @return string
     */
    protected function _getDefaultImage()
    {
        if ($this->_placeRoute === AW_Onsale_Block_Product_Label::CATEGORY_ROUTE) {
            if ($this->_type === AW_Onsale_Block_Product_Label::TYPE_ONSALE) {
                return AW_Onsale_Block_Product_Label::DEFAULT_IMAGE_ONSALE_CATEGORY;
            } elseif ($this->_type === AW_Onsale_Block_Product_Label::TYPE_NEW) {
                return AW_Onsale_Block_Product_Label::DEFAULT_IMAGE_NEW_CATEGORY;
            } else {
                return AW_Onsale_Block_Product_Label::DEFAULT_IMAGE_CUSTOM_CATEGORY;
            }
        } else {
            if ($this->_type === AW_Onsale_Block_Product_Label::TYPE_ONSALE) {
                return AW_Onsale_Block_Product_Label::DEFAULT_IMAGE_ONSALE_PRODUCT;
            } elseif ($this->_type === AW_Onsale_Block_Product_Label::TYPE_NEW) {
                return AW_Onsale_Block_Product_Label::DEFAULT_IMAGE_NEW_PRODUCT;
            } else {
                return AW_Onsale_Block_Product_Label::DEFAULT_IMAGE_CUSTOM_PRODUCT;
            }
        }
    }

    /**
     * Retrives position of label at product image
     * @return string
     */
    public function getPosition()
    {
        return Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'position', $this->_product);
    }

    /**
     * @param $position
     * @return array
     * First letter means vertical position. Second letter - horizontal
     */
    public function convertPosition($position)
    {
        if (in_array($position, array('ML', 'MC', 'MR'))) {
            $vertical = 'middle';
        } else {
            if (in_array($position, array('TL', 'TC', 'TR'))) {
                $vertical = 'top';
            } else {
                $vertical = 'bottom';
            }
        }
        if (in_array($position, array('BL', 'TL', 'ML'))) {
            $hor = 'left';
        } else {
            if (in_array($position, array('BC', 'TC', 'MC'))) {
                $hor = 'center';
            } else {
                $hor = 'right';
            }
        }
        $pos['v'] = $vertical;
        $pos['h'] = $hor;
        return $pos;
    }
}
