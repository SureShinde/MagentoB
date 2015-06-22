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

class AW_Customerattributes_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * @param string $version
     *
     * @return bool
     */
    public function checkMageVersion($version = '1.4.0.0')
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    /**
     * @param AW_Customerattributes_Model_Attribute_TypeAbstract $attributeType
     * @param string                                             $value
     *
     * @return boolean
     */
    public function isValueUnique(AW_Customerattributes_Model_Attribute_TypeAbstract $attributeType, $value)
    {
        $customer = Mage::helper('aw_customerattributes/customer')->getCustomer();
        if ($customer !== null) {
            /** @var Varien_Db_Adapter_Pdo_Mysql $connection */
            $connection = Mage::getSingleton('core/resource')->getConnection('read');

            $select = new Zend_Db_Select($connection);
            $select
                ->from(
                    Mage::getSingleton('core/resource')->getTableName('aw_customerattributes/value_varchar'),
                    array('count' => 'COUNT(value_id)')
                )
                ->where("value = ?", $value)
                ->where("attribute_id = ?", $attributeType->getAttribute()->getId())
                ->where("customer_id != ?", $customer->getId())
                ->group("value_id")
            ;

            if ($connection->fetchOne($select) > 0) {
                return false;
            }
        }
        return true;
    }
}