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
 * Attribute url backend
 */
class Belvg_Brands_Model_Brands_Url extends Varien_Object
{

    /**
     * Static URL instance
     *
     * @var Mage_Core_Model_Url
     */
    protected static $_url;

    /**
     * Retrieve URL Instance
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlInstance()
    {
        if (!self::$_url) {
            self::$_url = Mage::getModel('core/url');
        }

        return self::$_url;
    }

    /**
     * Retrieve Brand URL
     *
     * @param  Belvg_Brands_Model_Brands $brand
     * @param  bool $no_sid do not add SID mode
     * @return string
     */
    public function getBrandUrl($brand, $no_sid = TRUE)
    {
        $url = $this->getUrlInstance()->getUrl(Mage::helper('brands')->getRoute() . '/' . $brand->getUrlKey(), array(
                '_nosid' => $no_sid,
                '_store' => Mage::app()->getStore()->getId(),
                ));

        $url = rtrim($url, '/') . Mage::helper('catalog/product')->getProductUrlSuffix();
        return $url;
    }

}
