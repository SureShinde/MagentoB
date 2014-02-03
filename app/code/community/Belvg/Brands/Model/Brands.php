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
 * Brands model
 */
class Belvg_Brands_Model_Brands extends Mage_Core_Model_Abstract
{

    const CACHE_TAG = 'belvg_brands';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'brands';

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('brands/brands');
    }

    /**
     * Brands EAV attriute set id
     *
     * @return ind
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * validat model data
     *
     * @return boolean|array
     */
    public function validate()
    {
        $errors = array();
        $helper = Mage::helper('brands');
        if (!Zend_Validate::is($this->getTitle(), 'NotEmpty')) {
            $errors[] = $helper->__('Brand title can\'t be empty');
        }

        if (!Zend_Validate::is($this->getDescription(), 'NotEmpty')) {
            $errors[] = $helper->__('Description can\'t be empty');
        }

        if (empty($errors)) {
            return TRUE;
        }

        return $errors;
    }

    /**
     * Format brand data for save
     *
     * @return \Belvg_Brands_Model_Brands
     */
    public function prepareBrand()
    {
        $this->setTitle(trim($this->getTitle()));
        $this->setPagetitle(trim($this->getPagetitle()));
        return $this;
    }

    /**
     * Get product url model
     *
     * @return Belvg_Brands_Model_Brands_Url
     */
    public function getUrlModel()
    {
        if ($this->_urlModel === NULL) {
            $this->_urlModel = Mage::getSingleton('brands/brands_url');
        }

        return $this->_urlModel;
    }

    /**
     * Retrieve Brand URL
     *
     * @param  bool $no_sid
     * @return string
     */
    public function getUrl($no_sid = TRUE)
    {
        return $this->getUrlModel()->getBrandUrl($this, $no_sid);
    }

    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }

        return Mage::app()->getStore()->getId();
    }

}