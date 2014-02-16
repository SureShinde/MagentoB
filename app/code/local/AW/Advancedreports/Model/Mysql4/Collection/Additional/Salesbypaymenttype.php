<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_ARUnits/Salesbypaymenttype
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesbypaymenttype
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{

    /**
     * Add ordeer items count
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesbypaymenttype
     */
    public function addOrderItemsCount()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
            $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
            $this->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = main_table.entity_id AND item.parent_item_id IS NULL)", array('qty_ordered'=>'sum(item.qty_ordered)') )
                    ->group('main_table.entity_id')
                    ;
        } else {
            $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
            $this->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)", array('qty_ordered'=>'sum(item.qty_ordered)') )
                    ->group('e.entity_id')
                    ;
        }
        return $this;
    }

    /**
     * Add payment type
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesbypaymenttype
     */
    public function addPaymentMethod()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
            $salesOrderPayment = $this->_helper()->getSql()->getTable('sales_flat_order_payment');
            $this->getSelect()
                    ->join(array('salesPayment'=>$salesOrderPayment), "salesPayment.parent_id = main_table.entity_id", array('method'))
                    ;
        } else {
            $eavType = $this->_helper()->getSql()->getTable('eav_entity_type');
            $eavAttr = $this->_helper()->getSql()->getTable('eav_attribute');
            $orderEntity = $this->_helper()->getSql()->getTable('sales_order_entity');
            $paymentValue = $this->_helper()->getSql()->getTable('sales_order_entity_varchar');

            $this->getSelect()
                    ->join(array('eav_pay_type'=>$eavType), "(eav_pay_type.entity_type_code = 'order_payment')", array())
                    ->join(array('attr_pay'=>$eavAttr), "(attr_pay.entity_type_id = eav_pay_type.entity_type_id AND attr_pay.attribute_code = 'method')", array())
                    ->join(array('o_entity'=>$orderEntity), "(o_entity.parent_id = e.entity_id AND o_entity.entity_type_id = eav_pay_type.entity_type_id)", array())
                    ->join(array('val_pay'=>$paymentValue), "(val_pay.entity_id = o_entity.entity_id AND val_pay.attribute_id = attr_pay.attribute_id)", array('method'=>'value'))
                    ;
        }
        return $this;
    }


}