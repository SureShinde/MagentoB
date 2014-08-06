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
 * The default container class.
 */
class Brim_PageCache_Model_Container_Formkey extends Brim_PageCache_Model_Container_Abstract
{
    /**
     * Disables block update caching as no info in known about the blocks that may use this container.  This ensures
     * the block will be re-rendered for each page view.
     *
     * @return bool|string
     */
    protected function _getCacheId() {
        return false;
    }
}

