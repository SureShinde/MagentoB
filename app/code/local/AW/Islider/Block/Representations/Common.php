<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Block_Representations_Common extends Mage_Core_Block_Template {
    private $_awisblock = null;
    private $_slides = null;

    public function getSlides() {
        if($this->getAWISBlock() && $this->_slides === null) {
            $this->_slides = $this->getAWISBlock()->getImagesCollection();
            $this->_slides->sortBySortOrder()
                ->addActualDateFilter()
                ->addActiveFilter();
        }
        return $this->_slides;
    }

    public function setAWISBlock($block) {
        $this->_awisblock = $block;
        return $this;
    }

    public function getAWISBlock() {
        return $this->_awisblock;
    }

    public function canDisplay() {
        if($this->getAWISBlock()) {
            if(array_intersect($this->getAWISBlock()->getData('store'), array(0, Mage::app()->getStore()->getId())) == array()) return false;
            if(!$this->getAWISBlock()->getData('is_active')) return false;
            return true;
        }
        return false;
    }

    public function getUniqueBlockId() {
        if(is_null($this->_uniqueBlockId))
            $this->_uniqueBlockId = uniqid('awiSlider');
        return $this->_uniqueBlockId;
    }

    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false) {
        if(Mage::helper('awislider')->checkVersion('1.4.1.1'))
            return parent::stripTags($data, $allowableTags, $allowHtmlEntities);
        else
            return $this->htmlEscape($data);
    }
}
