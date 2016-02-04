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
 * @package    AW_Ascurl
 * @version    1.3.5
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Ascurl_Helper_Data extends Mage_Core_Helper_Abstract
{
    const USE_CANONICAL_LINK_FOR_PRODUCTS = 'catalog/advancedseo/use_canonical_link_for_products';
    const USE_CATEGORY_AT_URL             = 'catalog/seo/product_use_categories';

    protected $_storeId;
    protected $_storeCategories;

    public function insertStringToFilename($filename, $toInsert)
    {
        $dotPosition = strrpos($filename, '.');
        if ($dotPosition === false) {
            $filename = $filename.$toInsert;
        } else {
            $part1 = substr($filename, 0, $dotPosition);
            $part2 = substr($filename, $dotPosition);
            $filename = $part1.$toInsert.$part2;
        }
        return $filename;
    }

    public function useCanonicalUrl()
    {
        $resultToReturn = false;
        if ($this->isModuleOutputEnabled()
            && Mage::getStoreConfig(self::USE_CANONICAL_LINK_FOR_PRODUCTS)
        ) {
            $resultToReturn = true;
        }
        return $resultToReturn;
    }

    public function startEmulation($storeId, $area = Mage_Core_Model_App_Area::AREA_FRONTEND)
    {
        $emulateInfo = new Varien_Object;
        $emulateInfo->setStoreId(Mage::app()->getStore()->getId());
        Mage::app()->setCurrentStore($storeId);
        $initialDesign = Mage::getDesign()->setAllGetOld(array(
            'package' => Mage::getStoreConfig('design/package/name', $storeId),
            'store'   => $storeId,
            'area'    => $area
        ));
        $emulateInfo->setDesign($initialDesign);
        Mage::getDesign()->setTheme('');
        Mage::getDesign()->setPackageName('');
        return $emulateInfo;
    }

    public function stopEmulation(Varien_Object $emulateInfo)
    {
        Mage::app()->setCurrentStore($emulateInfo->getStoreId());
        Mage::getDesign()->setAllGetOld($emulateInfo->getDesign());
        Mage::getDesign()->setTheme('');
        Mage::getDesign()->setPackageName('');
        return $this;
    }

    public function getCategoryIdAsc($product, $forGoogleSitemap = false)
    {
        if (!$this->_storeId) {
            $this->_storeId = $product->getStoreId();
        }

        $category = null;
        if (!Mage::getStoreConfig(self::USE_CATEGORY_AT_URL, $this->_storeId)) {
            return $category;
        }
        
        if ($product->getCategoryIdAsc()) {
            $category = $product->getCategoryIdAsc();
        } else {
            if (!$forGoogleSitemap) {
                $productCategoriesIds = $product->getCategoryIds();
            } else {
                $productCategoriesIds = array();
                if ($product->getData('category_ids')) {
                    $productCategoriesIds = explode(',', $product->getData('category_ids'));
                }
            }
            
            foreach ($productCategoriesIds as $productCategoryId) {
                if (in_array($productCategoryId, $this->getCategoriesIdsForStore())) {
                    $category = $productCategoryId;
                    break;
                }
            }
        }
        return $category;
    }

    public function getCategoriesIdsForStore()
    {
        if (!$this->_storeCategories) {
            $this->_storeCategories = Mage::getModel('catalog/category')
                ->getCategories(Mage::app()->getStore($this->_storeId)->getRootCategoryId(), false, false, true)
                ->getItems()
            ;
        }

        if (is_array($this->_storeCategories)) {
            return array_keys($this->_storeCategories);
        }
        return array();
    }
}