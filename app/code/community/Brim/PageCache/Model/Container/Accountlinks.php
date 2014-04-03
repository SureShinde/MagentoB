<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file Brim-LLC-Magento-License.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_PageCache
 * @copyright  Copyright (c) 2011-2014 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */


class Brim_PageCache_Model_Container_Accountlinks extends Brim_PageCache_Model_Container_Abstract {

    const XML_REPLACEMENT_TEXT_LOG_LINK = '<BRIM_FPC_TOP_LINKS_LOG_LINK/>';

    protected $_sessionCacheKeyName = 'Accountlinks_Blockkey';

    protected function _generateCacheKey() {
        Varien_Profiler::start('Brim_PageCache::Brim_PageCache_Model_Container_Accountlinks::generateCacheKey');
        //
        $customer   = Mage::getSingleton('customer/session');
        $cacheKey   = $customer->getCustomerGroupId()
            . '_' . Mage::helper('wishlist')->getItemCount()
            . '_' . Mage::helper('checkout/cart')->getSummaryCount();
        Varien_Profiler::stop('Brim_PageCache::Brim_PageCache_Model_Container_Accountlinks::generateCacheKey');

        return $cacheKey;
    }


    protected function _createBlock() {

        $args   = $this->_blockArgs;

        if (isset($args['layout'])) {

            // Inject
            $customer   = Mage::getSingleton('customer/session');
            if ($customer->isLoggedIn()) {
$linkXml = <<<EOF
    <reference name="top.links">
        <action method="addLink" translate="label title" module="customer"><label>Log Out</label><url helper="customer/getLogoutUrl"/><title>Log Out</title><prepare/><urlParams/><position>100</position></action>
    </reference>
EOF;
            } else {
$linkXml = <<<EOF
    <reference name="top.links">
        <action method="addLink" translate="label title" module="customer"><label>Log In</label><url helper="customer/getLoginUrl"/><title>Log In</title><prepare/><urlParams/><position>100</position></action>
    </reference>
    <remove name="reorder"></remove>
EOF;
            }


            $args['layout'] = str_replace(self::XML_REPLACEMENT_TEXT_LOG_LINK, $linkXml, $args['layout']);

            $layout = new Mage_Core_Model_Layout($args['layout']);
            $layout->generateBlocks();
            $this->_block = $layout->getBlock($args['name']);
        }

        return $this->_block;
    }


    public static function getContainerArgs($block, $options = array()) {
        $defaultOptions = array(
            'block'     => get_class($block),
            'name'      => $block->getNameInLayout(),
            'template'  => $block->getTemplate(),
            'layout'    => self::_generateBlockLayoutXML($block->getNameInLayout()),
            'remove_l1' => false
        );

        $options = array_merge($defaultOptions, $options);

        return $options;
    }

    protected static function _generateBlockLayoutXML($blockName) {

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_CONDITIONS_USE_CTR_GRP)
            && !Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_CONDITIONS_CMB_CTR_GRP)) {
            return Brim_PageCache_Model_Container_Abstract::_generateBlockLayoutXML($blockName);
        }

        // get layout sections references our block
        if (self::$_updateCache == null) {
            self::$_updateCache = Mage::app()->getLayout()->getUpdate()->asSimplexml();
        }
        $sections   = self::$_updateCache->xpath("//block[@name='{$blockName}'] | //reference[@name='{$blockName}']");

        // convert section into it's own layout.
        $layoutXml  = "";
        foreach($sections as $section) {
            $placeholderAdded = false;

            $action = $section->action;
            if ($action && $action->getAttribute('method') == 'addLink') {
                if ($action->url) {
                    $helper = $action->url->getAttribute('helper');
                    if ($helper == 'customer/getLoginUrl' || $helper == 'customer/getLogoutUrl') {
                        // Adding placeholder
                        $layoutXml .= self::XML_REPLACEMENT_TEXT_LOG_LINK;
                        $placeholderAdded = true;
                    }
                }
            }

            if ($placeholderAdded === false) {
                $layoutXml .= self::_generateSubBlockLayoutXml($section);
            }
        }

        // Processes layout remove instructions
        $layout = new Mage_Core_Model_Layout();
        $layout->getUpdate()->addUpdate($layoutXml);
        $layout->generateXml();
        $layoutXml = $layout->getXmlString();

        return $layoutXml;
    }
}