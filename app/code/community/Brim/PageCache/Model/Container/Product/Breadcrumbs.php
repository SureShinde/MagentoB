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


class Brim_PageCache_Model_Container_Product_Breadcrumbs extends Brim_PageCache_Model_Container_Abstract {

    protected function _getCacheKey() {
        $key = Mage::registry('current_product')->getId();
        if (($category = Mage::registry('current_category')) != null) {
            $key .= '_' . $category->getId();
        }
        return $key;
    }

    protected function _createBlock() {
        $return = parent::_createBlock();

        $args   = $this->_blockArgs;
        $layout = new Mage_Core_Model_Layout($args['layout']);
        $layout->generateBlocks();

        $layout->createBlock('catalog/breadcrumbs');

        return ($this->_block = $layout->getBlock($args['name']));
    }
}
