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

class Brim_PageCache_Model_Adminhtml_System_Config_Source_Storage_Type
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Brim_PageCache_Model_Config::STORAGE_TYPE_FILE,
                'label'=>Mage::helper('brim_pagecache')->__('File')
            ),
            array(
                'value' => Brim_PageCache_Model_Config::STORAGE_TYPE_FILE_SCALE,
                'label'=>Mage::helper('brim_pagecache')->__('Scalable File')
            ),
            array(
                'value' => Brim_PageCache_Model_Config::STORAGE_TYPE_DATABASE,
                'label'=>Mage::helper('brim_pagecache')->__('Database')
            ),
            array(
                'value' => Brim_PageCache_Model_Config::STORAGE_TYPE_APC,
                'label'=>Mage::helper('brim_pagecache')->__('APC')
            ),
            array(
                'value' => Brim_PageCache_Model_Config::STORAGE_TYPE_MEMCACHED,
                'label'=>Mage::helper('brim_pagecache')->__('Memcached')
            ),
        );
    }
}
