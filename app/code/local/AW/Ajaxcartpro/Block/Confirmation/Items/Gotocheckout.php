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
 * @package    AW_Ajaxcartpro
 * @version    3.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ajaxcartpro_Block_Confirmation_Items_Gotocheckout extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if (!$this->_isAllowed()) {
            return '';
        }
        $this->setTemplate('ajaxcartpro/confirm/items/gotocheckout.phtml');
        return parent::_toHtml();
    }

    protected function _isAllowed()
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if (
            $request->getModuleName() === 'checkout' &&
            $request->getControllerName() === 'cart' &&
            !$this->getShowOnCartPage()
        ) {
            return false;
        }
        return true;
    }
}