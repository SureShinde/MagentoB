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
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Block_Adminhtml_Blocks_Edit_Tab_Automation_Categoriesgrid
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
    private $_afpVO = null;

    protected function _beforeToHtml()
    {
        $this->setTemplate('aw_featured/catalog/categories/tree.phtml');
        return $this;
    }

    public function getProduct()
    {
        if (is_null($this->_afpVO)) {
            $this->_afpVO = new Varien_Object();
        }
        if (!$this->_afpVO->getCategoryIds()) {
            $_data = Mage::getSingleton('adminhtml/session')->getData(AW_Featured_Helper_Data::FORM_DATA_KEY);
            if (!is_object($_data)) {
                $_data = new Varien_Object($_data);
            }
            if ($_data->getCategoryIds()) {
                $this->_afpVO->setCategoryIds(@explode(',', $_data->getCategoryIds()));
            } else {
                $this->_afpVO->setCategoryIds(array());
                $_automationData = $_data->getAutomationData();
                if ($_automationData && isset($_automationData['categories'])) {
                    $this->_afpVO->setCategoryIds(@explode(',', $_automationData['categories']));
                }
            }
        }
        return $this->_afpVO;
    }
}
