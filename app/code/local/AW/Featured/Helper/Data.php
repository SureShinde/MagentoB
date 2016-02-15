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
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Helper_Data extends Mage_Core_Helper_Abstract
{
    const FORM_DATA_KEY = 'awfeatured_form_data';
    const REGISTRY_ABSTRACT_BLOCK = 'awfeatured_product_abstract_block';
    const FORM_DUPLICATE_NAME = 'Duplicate_';
    const FORM_DUPLICATE_ID = 'duplicate_';

    public function checkVersion($version)
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    public function isHttps()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
    }

    public function removeEmptyItems($var)
    {
        return !empty($var);
    }

    public function prepareArray($var)
    {
        if (is_string($var)) {
            $var = @explode(',', $var);
        }
        if (is_array($var)) {
            $var = array_unique($var);
            $var = array_filter($var, array(Mage::helper('awfeatured'), 'removeEmptyItems'));
            $var = @implode(',', $var);
        }
        return $var;
    }

    public function getAbstractProductBlock()
    {
        $_abstractBlock = Mage::registry(self::REGISTRY_ABSTRACT_BLOCK);
        if (!$_abstractBlock) {
            $_abstractBlock = Mage::getSingleton('core/layout')->createBlock('catalog/product_list');
            $_abstractBlock->addPriceBlockType(
                'bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml'
            );
            Mage::register(self::REGISTRY_ABSTRACT_BLOCK, $_abstractBlock);
        }
        return $_abstractBlock;
    }
}
