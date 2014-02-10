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
 * @copyright  Copyright (c) 2011-2012 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */
 
class Brim_PageCache_Model_Container_Accountlinks extends Brim_PageCache_Model_Container_Abstract {

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
}