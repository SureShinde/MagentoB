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


class AW_Customerattributes_Model_Resource_Attribute_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_isLabelTableJoined = false;

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_customerattributes/attribute');
    }

    /**
     * @param int $storeId
     *
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function joinAttributeLabelsForStore($storeId = 0)
    {
        if (!$this->_isLabelTableJoined) {
            $conditions = array(
                "main_table.attribute_id = attribute_label.attribute_id",
                "attribute_label.store_id = {$storeId}"
            );
            $this->getSelect()->joinLeft(
                array('attribute_label' => $this->getTable('aw_customerattributes/label')),
                join(' AND ', $conditions),
                array('label' => 'value')
            );
            $this->_isLabelTableJoined = true;
        }
        return $this;
    }

    /**
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function addIsEnabledFilter()
    {
        $this->addFieldToFilter('is_enabled', array('eq' => 1));
        return $this;
    }

    /**
     * @param integer $storeId
     *
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function addStoreFilter($storeId)
    {
        $this->addFieldToFilter(
            'store_ids',
            array(
                array('finset' => $storeId),
            )
        );
        return $this;
    }

    /**
     * @param integer $group
     *
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function addCustomerGroupFilter($group)
    {
        $this->addFieldToFilter(
            'customer_groups',
            array(
                array('finset' => Mage_Customer_Model_Group::CUST_GROUP_ALL),
                array('finset' => $group),
            )
        );
        return $this;
    }

    /**
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function addDisplayOnCreateAccountPageFilter()
    {
        $this->addFieldToFilter(
            'display_on',
            array('finset' => AW_Customerattributes_Model_Source_Displayon::CREATE_ACCOUNT_PAGE_CODE)
        );
        return $this;
    }

    /**
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function addDisplayOnCustomerAccountPageFilter()
    {
        $this->addFieldToFilter(
            'display_on',
            array('finset' => AW_Customerattributes_Model_Source_Displayon::CUSTOMER_ACCOUNT_PAGE_CODE)
        );
        return $this;
    }

    /**
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function addEditableByCustomerFilter()
    {
        $this->addFieldToFilter('is_editable_by_customer', array('eq' => 1));
        return $this;
    }

    public function addLabelFilter($label, $storeId = 0)
    {
        $this->joinAttributeLabelsForStore($storeId);
        $this->addFieldToFilter('attribute_label.value', array('like' => $label));
        return $this;
    }

    /**
     * @param string $direction = Varien_Data_Collection::SORT_ORDER_DESC|Varien_Data_Collection::SORT_ORDER_ASC
     *
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function sortBySortOrder($direction = Varien_Data_Collection::SORT_ORDER_ASC)
    {
        $this->setOrder('sort_order', $direction);
        return $this;
    }

    public function addIsDisplayInGridFilter()
    {
        $this->addFieldToFilter('is_display_in_grid', array('eq' => 1));
        return $this;
    }
}