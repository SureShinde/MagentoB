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


/**
 * Navigation menu container.  Uses the category path for the cache key.
 */
class Brim_PageCache_Model_Container_Topmenu extends Brim_PageCache_Model_Container_Abstract
{
    protected function _construct($args) {
        // cat nav sub block bases the path on the entity key.  If not set root path is used.
        if (($category = Mage::registry('current_category'))) {
            Mage::register('current_entity_key', $category->getPath());
        }
    }

    protected function _getCacheKey() {
        return Mage::registry('current_entity_key');
    }
}

