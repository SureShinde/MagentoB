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
 * brands dropdown
 */
class Belvg_Brands_Block_List_Switch extends Belvg_Brands_Block_List
{

    /**
     * Get brands collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedBrandsCollection()
    {
        if (Mage::helper('brands')->isSwitcherActive()) {
            $collection = parent::getLoadedBrandsCollection();
        } else {
            $collection = new Varien_Data_Collection();
        }

        return $collection;
    }

    /**
     * get number of selected rands
     *
     * @return int
     */
    public function getBrandsCount()
    {
        return $this->getLoadedBrandsCollection()->count();
    }

    /**
     * Do not modify layout
     *
     * @return NULL
     */
    protected function _prepareLayout()
    {
        return NULL;
    }

    /**
     * If brand is selected - mark it as active
     *
     * @return int
     */
    public function getCurrentBrandId()
    {
        if (Mage::registry('brands_data')) {
            return Mage::registry('brands_data')->getId();
        }

        return 0;
    }

}
