<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Clickcheckout
 * @version    1.1.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Clickcheckout_Model_Nocache extends Enterprise_PageCache_Model_Container_Abstract {

    protected function _getCacheId() {
        return 'CUSTOMER_STATE_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, ''));
    }

    protected function _renderBlock() {
        if (null !== $this->_getProductId()) {
            if (Mage::registry('current_product')) {
                Mage::unregister('current_product');
            }
            $product = Mage::getModel('catalog/product')->load($this->_getProductId());
            Mage::register('current_product', $product);
        }
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $block = new $block;
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());
        return $block->toHtml();
    }
}