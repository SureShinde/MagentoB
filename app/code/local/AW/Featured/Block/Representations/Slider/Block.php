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
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Block_Representations_Slider_Block extends AW_Featured_Block_Representations_Common
{
    protected function _beforeToHtml()
    {
        if ($this->getTemplate()) {
            return parent::_beforeToHtml();
        }
        $this->setTemplate('aw_featured/slider/vertical.phtml');
        $layoutType = $this->getAFPBlockTypeData('layout');
        if ($layoutType == AW_Featured_Model_Representations_Slider_Layouts::LAYOUT_HORIZONTAL) {
            $this->setTemplate('aw_featured/slider/horizontal.phtml');
        }
    }
    
    public function getContainerStyleString()
    {
        $res = sprintf('width: %s;', $this->getWidth());
        if ($this->getHeight()) {
            $res .= sprintf('height: %spx;', $this->getHeight());
        }
        return $res;
    }
    
    public function getSwitchEffect()
    {
        return $this->getAFPBlockTypeData('switcheffect');
    }
    
    public function getWidth()
    {
        return $this->getAFPBlockTypeData('width') ? $this->getAFPBlockTypeData('width').'px' : '100%';
    }
    
    public function getHeight()
    {
        return $this->getAFPBlockTypeData('height');
    }
    
    public function getShowProductName()
    {
        return (bool)$this->getAFPBlockTypeData('showproductname');
    }

    public function getShowDetails()
    {
        return (bool)$this->getAFPBlockTypeData('showdetails');
    }

    public function getDetailsFromProduct(Mage_Catalog_Model_Product $product)
    {
        $shortDescriptionAttribute = Mage::getSingleton('eav/config')->getAttribute(
            'catalog_product', 'short_description'
        );
        if (null === $shortDescriptionAttribute->getData('is_html_allowed_on_front')
            || null === $shortDescriptionAttribute->getData('is_wysiwyg_enabled')) {
            Mage::getSingleton('eav/config')->clear();
        }
        $shortDescription = Mage::helper('catalog/output')->productAttribute(
            $product, nl2br($product->getShortDescription()), 'short_description'
        );
        return $shortDescription;
    }

    public function getShowPrice()
    {
        return (bool)$this->getAFPBlockTypeData('showprice');
    }

    public function getShowAddToCartButton()
    {
        return (bool)$this->getAFPBlockTypeData('showaddtocart');
    }
    
    public function getAnimationSpeed()
    {
        return $this->getAFPBlockTypeData('animationspeed');
    }

    public function getAutoHideNavi()
    {
        return $this->getAFPBlockTypeData('autohidenavi');
    }
}
