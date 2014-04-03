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


class Brim_Pagecache_Block_Adminhtml_System_Config_Form_Field_Handles extends Mage_Core_Block_Html_Select
{
    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml() {
        if (!$this->getOptions()) {
            // Reuse widget code to collect layout handles.
            /** @var Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Layout $layoutChooser */
            $layoutChooser  = $this->getLayout()->createBlock(
                'brim_pagecache/adminhtml_system_config_chooser_layout'
            );

            $handles        = $layoutChooser->getLayoutHandles(
                $layoutChooser->getArea(),
                $layoutChooser->getPackage(),
                $layoutChooser->getTheme()
            );

            foreach ($handles as $handle => $label) {
                $this->addOption($handle, $label);
            }
        }
        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
