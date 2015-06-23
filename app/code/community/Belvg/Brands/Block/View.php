<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 * Brands view page
 */
class Belvg_Brands_Block_View extends Mage_Core_Block_Template
{
    /**
     * Init block caching
     */
    protected function _construct()
    {
        $this->addData(array(
                'cache_lifetime' => -1,
                'cache_tags' => array($this->getCacheTag . '_' . $this->getCurrentBrand()->getId()),
                'cache_key' => $this->getCurrentBrand()->getId()
        ));
    }

    /**
     * Set title, breadcrumbs, meta data
     *
     * @return Belvg_Brands_Block_View
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $brand = $this->getCurrentBrand();
            if ($brand->getPagetitle()) {
                $this->__($title = $brand->getPagetitle());
            } else {
                $title = sprintf('%s / %s', $brand->getTitle(), Mage::helper('brands')->getPageTitle());
            }

            $headBlock->setTitle($title);

            if ($breadcrumbs) {
                $title = $brand->getTitle();

                $breadcrumbs->addCrumb('home', array(
                        'label' => $this->__('Home'),
                        'title' => $this->__('Go to Home Page'),
                        'link' => Mage::getBaseUrl()
                ))->addCrumb('brands', array(
                        'label' => $this->__(Mage::helper('brands')->getPageTitle()),
                        'title' => $this->__(Mage::helper('brands')->getPageTitle()),
                        'link' => Mage::getUrl(Mage::helper('brands')->getRoute())
                ))->addCrumb('item', array(
                        'label' => $title,
                        'title' => $title,
                ));
            }

            if ($description = $brand->getPagedescription()) {
                $headBlock->setDescription($description);
            }

            if ($keywords = $brand->getPagekeyword()) {
                $headBlock->setKeywords($keywords);
            }
        }

        return $this;
    }

    /**
     * Return generated html for the list of products, associated with current brand
     *
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list', FALSE);
    }

    /**
     * Retrieve current brand model object
     *
     * @return Belvg_Brands_Model_Brands
     */
    public function getCurrentBrand()
    {
        if (!$this->hasData('brands_data')) {
            $this->setData('brands_data', Mage::registry('brands_data'));
        }

        return $this->getData('brands_data');
    }

}
