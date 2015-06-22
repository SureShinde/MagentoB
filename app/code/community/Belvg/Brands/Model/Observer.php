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
 *  Brands events observer
 */
class Belvg_Brands_Model_Observer
{

    /**
     * Flush cahe on configuration changes
     *
     * @param  Varien_Event_Observer $observer
     * @return Belvg_Brands_Model_Observer
     */
    public function onConfigUpdate(Varien_Event_Observer $observer)
    {
        $cache_tags = array(
                Mage_Catalog_Model_Category::CACHE_TAG,
                Mage_Core_Model_Store_Group::CACHE_TAG,
                Belvg_Brands_Model_Brands::CACHE_TAG,
        );

        // Use In Layered Navigation:
        //  0 - no,
        //  1 - filterable with results
        //  2 - filterable without results
        $flag = Mage::getStoreConfig('brands/settings/enabled') ? 1 : 0;

        $installer = Mage::getResourceModel('catalog/setup', 'default_setup');
        $installer->updateAttribute('catalog_product', 'brands', 'is_filterable', $flag);

        Mage::app()->cleanCache($cache_tags);
        return $this;
    }

    /**
     * Flush cahe on saing brand
     *
     * @param  Varien_Event_Observer $observer
     * @return Belvg_Brands_Model_Observer
     */
    public function onBrandUpdate(Varien_Event_Observer $observer)
    {
        $brand = $observer->getBrand();
        $brand_id = $brand->getId();

        $cache_tags = array(
                Mage_Catalog_Model_Category::CACHE_TAG,
                Mage_Core_Model_Store_Group::CACHE_TAG,
                Belvg_Brands_Model_Brands::CACHE_TAG,
                Belvg_Brands_Model_Brands::CACHE_TAG . '_' . $brand_id,
        );

        Mage::app()->cleanCache($cache_tags);
        return $this;
    }

    /**
     * Revalidate cache on saving product
     *
     * @param Varien_Event_Observer $observer
     * @return Belvg_Brands_Model_Observer
     */
    public function onProductSave(Varien_Event_Observer $observer)
    {
        $_product = $observer->getProduct();
        $_orig_brand_id = $_product->getOrigData('brands') ;
        $_brand_id = $_product->getData('brands') ;

        if ($_orig_brand_id != $_brand_id) {
            if (!empty($_brand_id)) {
                $brand_id = $_brand_id;
            }

            if (!empty($_orig_brand_id)) {
                $brand_id = $_orig_brand_id;
            }

            $cache_tags = array(
                    Mage_Catalog_Model_Category::CACHE_TAG,
                    Mage_Core_Model_Store_Group::CACHE_TAG,
                    Belvg_Brands_Model_Brands::CACHE_TAG,
                    Belvg_Brands_Model_Brands::CACHE_TAG . '_' . $brand_id,
                    Mage_Catalog_Model_Product::CACHE_TAG . $_product->getId()
            );

            Mage::app()->cleanCache($cache_tags);
        }

        return $this;
    }

}
