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
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Customerattributes_Block_Customer_Form_Edit extends Mage_Core_Block_Template
{
    protected $_valueData = null;

    /**
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function getAttributeCollection()
    {
        $customer = Mage::helper('customer')->getCustomer();
        return Mage::helper('aw_customerattributes/customer')->getAttributeCollectionForCustomerEdit($customer);
    }

    public function getValueByAttributeId($attributeId)
    {
        if (is_null($this->_valueData)) {
            $this->_valueData = array();
            $customer = Mage::helper('customer')->getCustomer();
            $collection = Mage::helper('aw_customerattributes/customer')
                ->getAttributeValueCollectionForCustomer($customer);
            foreach ($collection as $item) {
                $this->_valueData[$item->getData('attribute_id')] = $item->getData('value');
            }
        }
        return isset($this->_valueData[$attributeId]) ? $this->_valueData[$attributeId] : null;
    }
}