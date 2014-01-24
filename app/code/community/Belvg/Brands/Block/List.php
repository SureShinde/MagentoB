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
 * List of existing brands
 */
class Belvg_Brands_Block_List extends Mage_Core_Block_Template
{

    /**
     * Init block caching
     */
    protected function _construct()
    {
        $this->addData(array(
                'cache_lifetime' => FALSE,
                'cache_tags' => array(Belvg_Brands_Model_Brands::CACHE_TAG),
        ));
    }

    /**
     * cached brands collection
     *
     * @var Belvg_Brands_Model_Resource_Eav_Mysql4_Brands_Collection
     */
    protected $_brands_collection;

    /**
     * Get brands collection
     *
     * @return Belvg_Brands_Model_Resource_Eav_Mysql4_Brands_Collection
     */
    protected function _getBrandsCollection()
    {
        if (is_null($this->_brands_collection)) {
            $collection = Mage::getModel('brands/brands')->getCollection();
            $collection->addAttributeToSelect(array('title', 'description', 'url_key', 'image'))
                    ->addAttributeToFilter('active', 1)
                    ->setStore(Mage::app()->getStore())
                    ->setOrder('title', 'ASC');

            $this->_brands_collection = $collection;
        }

        return $this->_brands_collection;
    }

    /**
     * Get brands collection
     *
     * @return Belvg_Brands_Model_Resource_Eav_Mysql4_Brands_Collection
     */
    public function getLoadedBrandsCollection()
    {
        return $this->_getBrandsCollection();
    }

    /**
     * Set title, breadcrumbs
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $title = $this->__(Mage::helper('brands')->getPageTitle());

        if ($breadcrumbs) {
            $breadcrumbs->addCrumb('home', array(
                    'label' => $this->__('Home'),
                    'title' => $this->__('Go to Home Page'),
                    'link' => Mage::getBaseUrl()
            ))->addCrumb('brands', array(
                    'label' => $title,
                    'title' => $title
            ));
        }

        // modify page title
        $this->getLayout()->getBlock('head')->setTitle($title);

        return parent::_prepareLayout();
    }

    /**
     * Get brands list mode
     *
     * @return string
     */
    public function getMode()
    {
        return Mage::helper('brands')->getListMode();
    }

    /**
     * Get number of columns for brands list (grid mode)
     *
     * @return int
     */
    public function getColumnCount()
    {
        return Mage::helper('brands')->getColumnCount();
    }
}
