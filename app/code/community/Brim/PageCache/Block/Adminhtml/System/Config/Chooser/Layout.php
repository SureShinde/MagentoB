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



class Brim_PageCache_Block_Adminhtml_System_Config_Chooser_Layout
    extends Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Layout {

    protected $_layoutHandlePatterns = array(
        '^default$',
        '^PRODUCT_*',
        '^sales_*',
        '^checkout_*',
        '^customer_*',
        '^page_*',
        '^print*',
        '^rss_*',
        '^wishlist_*',
        '^oauth_*',
        '^shipping_*',
        '^xmlconnect_*',
        '^paypal*',
    );

    protected $_excludeLabelPattern = '/(My\sAccount)/i';

    /**
     * Filters out labels.
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return array
     */
    public function getLayoutHandles($area, $package, $theme)
    {
        if (empty($this->_layoutHandles)) {
            parent::getLayoutHandles($area, $package, $theme);

            foreach ($this->_layoutHandles as $key => $value) {
                if (preg_match($this->_excludeLabelPattern, $value) > 0) {
                    unset($this->_layoutHandles[$key]);
                }
            }
        }
        return $this->_layoutHandles;
    }
}