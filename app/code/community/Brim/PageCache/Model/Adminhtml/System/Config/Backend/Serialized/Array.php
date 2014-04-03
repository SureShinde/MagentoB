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


 
class Brim_PageCache_Model_Adminhtml_System_Config_Backend_Serialized_Array
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array {


    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();

            try {
                if (!empty($value) && strpos($value, '{') === false && strpos($value, '}') == false) {
                    $value = $this->_valueCompatibility($this->getPath(), $value);
                    $value = serialize($value);
                }
            } catch (Exception $e) {
                Mage::log("Unable to upgrade {$this->getPath()}: {$this->getValue()}");
            }

            $this->setValue(empty($value) ? false : unserialize($value));
        }
    }

    protected function _valueCompatibility($path, $value){
        $newValue   = array();
        if ($path == 'brim_pagecache/layout/custom_block_updates') {
            // Old value need to update
            $array      = explode("\n", $value);
            foreach ($array as $row) {
                $col1 = $col2 = null;
                @list($col1, $col2) = explode(',', $row);

                $newValue[uniqid('blockudpates', true)] = array(
                    'block_name'    => $col1,
                    'container'     => $col2
                );
            }
            $value = $newValue;
        } else if ($path == 'brim_pagecache/conditions/session_vars') {
            // Old value need to update
            $array = preg_split("/[\n\r]+/mi", $value);
            foreach ($array as $row) {
                $col1 = $col2 = null;
                @list($col1, $col2) = explode(':', $row);

                $newValue[uniqid('sesssionvars', true)] = array(
                    'variable'  => $col1,
                    'model'     => $col2
                );
            }
            $value = $newValue;
        } else if ($path == 'brim_pagecache/conditions/ignore_params') {
            $array      = explode(",", $value);
            foreach ($array as $row) {
                $newValue[uniqid('ignore_params', true)] = array(
                    'parameter'  => $row,
                );
            }
            $value = $newValue;
        }

        return $value;
    }

    protected function _converter() {

    }

}