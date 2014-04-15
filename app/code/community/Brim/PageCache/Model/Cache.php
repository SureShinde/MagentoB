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


class Brim_PageCache_Model_Cache extends Mage_Core_Model_Cache
{
    public function __construct(array $options = array())
    {
        if (!empty($options['backend_options']['cache_dir'])) {
            $cacheDir = $options['backend_options']['cache_dir'];
            if (substr($cacheDir, 0, 1) != '/') {
                $options['backend_options']['cache_dir'] = Mage::getBaseDir('var') . DS . $cacheDir;
            }
        }

        if (!empty($options['slow_backend_options']['cache_dir'])) {
            $cacheDir = $options['slow_backend_options']['cache_dir'];
            if (substr($cacheDir, 0, 1) != '/') {
                $options['slow_backend_options']['cache_dir'] = Mage::getBaseDir('var') . DS . $cacheDir;
            }
        }

        parent::__construct($options);
    }
}