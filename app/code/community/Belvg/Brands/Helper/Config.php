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
 * Get config params
 */
class Belvg_Brands_Helper_Config extends Mage_Core_Helper_Data
{

    /**
     * Module enabed
     */
    const XML_PATH_BRANDS_ENABLED = 'brands/settings/enabled';

    /**
     * Brands frontend route
     */
    const XML_PATH_BRANDS_ROUTE = 'brands/settings/route';

    /**
     * Brands frontend title
     */
    const XML_PATH_BRANDS_TITLE = 'brands/settings/title';

    /**
     * Default image width
     */
    const XML_PATH_BRANDS_IMAGE_WIDTH = 'brands/settings/image_width';

    /**
     * Default image height
     */
    const XML_PATH_BRANDS_IMAGE_HEIGHT = 'brands/settings/image_height';

    /**
     * Brands list view layout code
     */
    const XML_PATH_BRANDS_LIST_LAYOUT = 'brands/list/layout';

    /**
     * Brands view page layout code
     */
    const XML_PATH_BRANDS_VIEW_LAYOUT = 'brands/view/layout';

    /**
     * Output type for brands list
     */
    const XML_PATH_BRANDS_LIST_MODE = 'brands/list/list_mode';

    /**
     * Number of columns in grid brands list
     */
    const XML_PATH_BRANDS_LIST_COUNT = 'brands/list/column_count';

    /**
     * Whether to enable dropdown with all brands in column
     */
    const XML_PATH_BRANDS_LIST_SWITCHER = 'brands/list/switcher';

    /**
     * Whether to enable frontend menu item
     */
    const XML_PATH_BRANDS_LIST_MENU = 'brands/list/menu';

    /**
     * Check if module enabed
     *
     * @param mixed $store
     * @return boolen
     */
    public function isActive($store = '')
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_BRANDS_ENABLED, $store);
    }

    /**
     * Brands frontend route
     *
     * @param mixed $store
     * @return string
     */
    public function getRoute($store = '')
    {
        return Mage::getStoreConfig(self::XML_PATH_BRANDS_ROUTE, $store);
    }

    /**
     * Brands frontend title
     *
     * @param mixed $store
     * @return string
     */
    public function getPageTitle($store = '')
    {
        return Mage::getStoreConfig(self::XML_PATH_BRANDS_TITLE, $store);
    }

    /**
     * Default image width
     *
     * @param mixed $store
     * @return int
     */
    protected function getImageWidth($store = '')
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_BRANDS_IMAGE_WIDTH, $store);
    }

    /**
     * Default image height
     *
     * @param type $store
     * @return int
     */
    protected function getImageHeight($store = '')
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_BRANDS_IMAGE_HEIGHT, $store);
    }

    /**
     * Output type for brands list
     *
     * @param mixed $store
     * @return string
     */
    public function getListMode($store = '')
    {
        return Mage::getStoreConfig(self::XML_PATH_BRANDS_LIST_MODE, $store);
    }

    /**
     * Number of columns in grid brands list
     *
     * @param mixed $store
     * @return int
     */
    public function getColumnCount($store = '')
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_BRANDS_LIST_COUNT, $store);
    }

    /**
     * Brands list page layout code
     *
     * @param mixed $store
     * @return string
     */
    public function getListLayout($store = '')
    {
        return Mage::getStoreConfig(self::XML_PATH_BRANDS_LIST_LAYOUT, $store);
    }

    /**
     * Brands view page layout code
     *
     * @param mixed $store
     * @return string
     */
    public function getViewLayout($store = '')
    {
        return Mage::getStoreConfig(self::XML_PATH_BRANDS_VIEW_LAYOUT, $store);
    }

    /**
     * Whether to enable dropdown with all brands in column
     *
     * @param mixed $store
     * @return boolean
     */
    public function isSwitcherActive($store = '')
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_BRANDS_LIST_SWITCHER, $store);
    }

    /**
     * Whether to enable frontend menu item
     *
     * @param mixed $store
     * @return boolean
     */
    public function isMenuActive($store = '')
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_BRANDS_LIST_MENU, $store);
    }

}