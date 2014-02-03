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
 * Brands frontend controller
 */
class Belvg_Brands_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * Brands list
     *
     * @return void
     */
    public function indexAction()
    {
        Mage::helper('brands')->renderListLayout($this);
    }

    /**
     * Brand view page
     *
     * @return void
     */
    public function viewAction()
    {
        $this->_initBrand();
        Mage::helper('brands')->renderViewLayout($this);
    }

    /**
     * Get current brand
     *
     * @return void
     */
    protected function _initBrand()
    {
        $brand_id = (int) $this->getRequest()->getParam('id', 0);
        $store_id = Mage::app()->getStore()->getId();
        $brand = Mage::getModel('brands/brands')->setStoreId($store_id)->load($brand_id);
        /* @var $brand Belvg_Brands_Model_Brands */

        if ($brand->getId()) {
            Mage::register('brands_data', $brand);
        }
    }

}
