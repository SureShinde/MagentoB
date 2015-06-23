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


class AW_Onsale_Block_Product_View_Label extends AW_Onsale_Block_Product_Label
{

    protected $_label = null;
    protected $_product = null;

    public function setProduct($product)
    {
        $this->_product = $product;

        return $this;
    }

    public function isShow()
    {
        return (bool) ($this->getLabel()->getData());
    }

    public function getLabel()
    {
        if ($this->_label === null) {

            $websiteId = Mage::helper('onsale')->getWebsiteId();
            $customerGroupId = Mage::helper('onsale')->getCustomerGroupId();

            $label = Mage::getModel('onsale/label')
                    ->getForProductPage($this->getProduct(), $websiteId, $customerGroupId);

            $this->_label = $label;
        }

        return $this->_label;
    }

    public function setLabel($label)
    {
        $this->_label = $label;

        return $this;
    }

    public function getPosition()
    {
        return $this->getLabel()->getPosition();
    }

    public function getImageUrl()
    {
        return $this->getLabel()->getImageUrl();
    }

    public function getImageSizeHtml()
    {
        list($__w, $__h) = $this->getLabel()->getImageSize();

        return 'width: ' . $__w . 'px; height: ' . $__h . 'px;';
    }

    public function getType()
    {
        return 'custom';
    }

    /**
     * Retrives formatted label text
     * @return string
     */
    public function getFormatedText()
    {
        $this->_setProductVariables();

        $pattern = $this->getLabel()->getText();
        $pattern = $this->getFormatedCommonText($pattern);
        $pattern = $this->getFormatedCustomText($pattern);
        return $pattern;
    }

    /**
     * Set target product for rendering of label
     * @return AW_Onsale_Block_Product_Label
     */
    protected function _setProductVariables()
    {
        $product = $this->getProduct();
        # Fix #2111
        if (!$product->getCreatedAt()) {
            $product->setCreatedAt(Mage::helper('onsale')->getCustomAttributeValue('created_at', $product));
        }
        # End fix #2111
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
        $this->_inStock = (int) Mage::helper('onsale')->getStockAttribute('qty', $product);
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
        return $this;
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

}