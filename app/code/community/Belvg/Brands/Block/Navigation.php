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
 * Add top mnu item
 */
class Belvg_Brands_Block_Navigation extends Mage_Catalog_Block_Navigation
{

    /**
     * Init block caching
     */
    protected function _construct()
    {
        $this->addData(array(
                'cache_lifetime' => FALSE,
                'cache_tags' => array(
                        Mage_Catalog_Model_Category::CACHE_TAG,
                        Mage_Core_Model_Store_Group::CACHE_TAG,
                        Belvg_Brands_Model_Brands::CACHE_TAG),
        ));
    }

    /**
     * Inject new menu item into the top menu
     *
     * @param int $level
     * @param string $outermostItemClass
     * @param string $childrenWrapClass
     * @return string
     */
    public function renderCategoriesMenuHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        $active = ($this->getRequest()->getRouteName() == 'brands'?'active':'');

        // Get navigation menu html
        $html = parent::renderCategoriesMenuHtml($level, $outermostItemClass, $childrenWrapClass);

        // if module is active
        if (Mage::helper('brands')->isActive() && Mage::helper('brands')->isMenuActive()) {
            $html .= $this->getLayout()
                    ->createBlock('core/template')
                    ->setTemplate($this->getItemTemplate())
                    ->setItems($this->getBrands())
                    ->setActive($active)
                    ->setOutermostItemClass($outermostItemClass)
                    ->toHtml();
        }

        return $html;
    }

    /**
     * Get list of brands
     *
     * @return Belvg_Brands_Model_Resource_Eav_Mysql4_Brands_Collection
     */
    protected function getBrands()
    {
        $collection = Mage::getModel('brands/brands')->getCollection();
        /* @var $installer Belvg_Brands_Model_Resource_Eav_Mysql4_Brands_Collection */

        $collection->addAttributeToSelect(array('title', 'url_key'))
                ->addAttributeToFilter('active', 1)
                ->setStore(Mage::app()->getStore())
                ->setOrder('title', 'ASC');
        return $collection;
    }

}