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


class AW_Customerattributes_Model_Resource_Value_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_tableAliases = array();

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_customerattributes/value');
    }

    /**
     * Adding item to item array
     * FIX duplicate id in collection
     *
     * @param   Varien_Object $item
     *
     * @return  AW_Customerattributes_Model_Resource_Value_Collection
     */
    public function addItem(Varien_Object $item)
    {
        $this->_items[] = $item;
        return $this;
    }

    /**
     * @param Varien_Db_Select $select
     *
     * @return string|Varien_Db_Select
     */
    protected function _prepareSelect(Varien_Db_Select $select)
    {
        //Hack for fix magento bug
        parent::_prepareSelect($select);
        return $select;
    }

    /**
     * @param Zend_Db_Select $select
     *
     * @return array|void
     */
    protected function _fetchAll($select)
    {
        $uSelects = $this->getResource()->getSelectsFromMainSelect($select);
        $gSelect = $this->_conn->select();
        $gSelect->union($uSelects);
        return parent::_fetchAll($gSelect);
    }

    /**
     * @param $customerId
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addCustomerFilter($customerId)
    {
        $this->addFieldToFilter('customer_id', array('eq' => $customerId));
        return $this;
    }

    /**
     * @param $attributeId
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addAttributeFilter($attributeId)
    {
        $this->addFieldToFilter('attribute_id', array('eq' => $attributeId));
        return $this;
    }
}