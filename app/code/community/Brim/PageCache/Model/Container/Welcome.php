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

 
class Brim_PageCache_Model_Container_Welcome
    extends Brim_PageCache_Model_Container_Abstract {

    protected function _generateCacheKey() {
        return 'WELCOME_' . Mage::getSingleton('customer/session')->getCustomerId();
    }

    /**
     * Note: Mage 1.8 CE moved the welcome text to it's own block.  If the block exists we use it otherwise we fall back
     * @return string
     */
    protected function _renderBlock() {
        if ($this->_block != null) {
            $welcome = parent::_renderBlock();
        } else {
            $welcome = Mage::app()->getLayout()->createBlock('page/html_header')->getWelcome();
        }
        return $welcome;
    }

    /**
     * Marks the welcome content. Required since the welcome content is not it's own block, but
     * a method on the page/html_header block.
     *
     * Mage CE 1.8+ no longer uses this method.
     *
     * @static
     * @param $block
     * @return void
     */
    static public function setWelcomeWrapper($block) {
        $args = array(
            'name'      => 'welcome_container',
            'container' => 'Brim_PageCache_Model_Container_Welcome'
        );

        $welcome = Mage::getSingleton('brim_pagecache/engine')->markContent($args, $block->getWelcome());

        $block->setWelcome($welcome);
    }
}