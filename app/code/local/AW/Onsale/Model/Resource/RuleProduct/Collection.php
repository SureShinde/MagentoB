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


class AW_Onsale_Model_Resource_RuleProduct_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('onsale/label', 'rule_product_id');
    }

    public function addRuleDataForProductPage()
    {

        $columns = array(
            'position' => 'rule_table.product_page_position',
            'image' => 'rule_table.product_page_image',
            'image_path' => 'rule_table.product_page_img_path',
            'text' => 'rule_table.product_page_text',
        );

        $this->getSelect()->join(
                array('rule_table' => $this->getTable('onsale/rule')), 'main_table.rule_id = rule_table.rule_id', $columns
        );
        return $this;
    }

    public function addRuleDataForCategoryPage()
    {

        $columns = array(
            'position' => 'rule_table.category_page_position',
            'image' => 'rule_table.category_page_image',
            'image_path' => 'rule_table.category_page_img_path',
            'text' => 'rule_table.category_page_text',
        );

        $this->getSelect()->join(
                array('rule_table' => $this->getTable('onsale/rule')), 'main_table.rule_id = rule_table.rule_id', $columns
        );
        return $this;
    }

    public function addCustomerGroupId()
    {
        $columns = array(
            'customer_group.customer_group_id' => 'customer_group.customer_group_id',
        );

        $this->getSelect()->join(
                array('customer_group' => $this->getTable('onsale/customer_group'),)
                , 'main_table.rule_id = customer_group.rule_id'
                , $columns
        );
        return $this;
    }

    public function addTimeFilter()
    {
        $time = Mage::getModel('core/date')->gmtTimestamp();
        $this
                ->addFieldToFilter('main_table.from_time', array(
                    array('eq' => '0'),
                    array('lt' => $time)
                ))
                ->addFieldToFilter('main_table.to_time', array(
                    array('eq' => '0'),
                    array('gt' => $time)
                ));

        return $this;
    }

    /**
     * Add order field
     *
     * @param string $field
     * @param string $order
     * @return AW_Onsale_Model_Resource_RuleProduct_Collection
     */
    public function addOrderField($field, $order = self::SORT_ORDER_ASC)
    {
        $this->setOrder($field, $order);

        return $this;
    }

}