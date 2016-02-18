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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onsale_Block_Product_List_Label extends AW_Onsale_Block_Product_View_Label
{

    public function getLabel()
    {
        if ($this->_label === null) {

            $websiteId = Mage::helper('onsale')->getWebsiteId();
            $customerGroupId = Mage::helper('onsale')->getCustomerGroupId();

            $label = Mage::getModel('onsale/label')
                    ->getForCategoryPage(
                    $this->getProduct(), $websiteId, $customerGroupId, Mage::helper('onsale')->getCurrentProductIds()
            );
            $this->_label = $label;
        }

        return $this->_label;
    }

}