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
 * @package    AW_ARUnits/Manufacturer
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Advancedreports_Model_Mysql4_Collection_Additional_Manufacturer
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{

    /**
     * Reinitialize select
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Manufacturer
     */
    public function reInitSelect()
    {
        $filterField = $this->_helper()->confOrderDateFilter();
        if($filterField == "invoice_created_at"){
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order_status_history');
            
	        $this->getSelect()->reset();
	        $this->getSelect()->from(array($this->_getSalesCollectionTableAlias()=>$orderTable), array(
	            'order_created_at' => 'created_at',
	            'order_id' => 'parent_id',
	        ));
        }else{
	        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
	            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
	        } else {
	            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
	        }

	        $this->getSelect()->reset();
	        $this->getSelect()->from(array($this->_getSalesCollectionTableAlias()=>$orderTable), array(
	            'order_created_at' => $filterField,
	            'order_id' => 'entity_id',
	            'order_increment_id' => 'increment_id',
	        ));
        }
        return $this;
    }

    /**
     * Add order items
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Manufacturer
     */
    public function addOrderItems()
    {
        $filterField = $this->_helper()->confOrderDateFilter();
        if($filterField == "invoice_created_at"){
            $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
            $this->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = main_table.entity_id AND item.parent_item_id IS NULL)" )
                    ->order("sales_flat_order_status_history.created_at DESC")
                    ;
        }else{
	        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
	            $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
	            $this->getSelect()
	                    ->join( array('item'=>$itemTable), "(item.order_id = main_table.entity_id AND item.parent_item_id IS NULL)" )
	                    ->order("main_table.{$filterField} DESC")
	                    ;
	        } else {
	            $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
	            $this->getSelect()
	                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)" )
	                    ->order("e.{$filterField} DESC")
	                    ;
	        }
        }

        $this->getSelect()
                    # subtotal
                    ->columns(array('base_row_subtotal'=>"( item.qty_ordered * item.base_price )"))

                    # total
                    ->columns(array('base_row_xtotal_incl_tax'=>"(item.qty_ordered * (item.base_price - ABS(item.base_discount_amount)) + item.base_tax_amount)"))
                    ->columns(array('base_tax_xamount'=>"(item.base_tax_amount)"))
                    ->columns(array('base_row_xtotal'=>"( item.qty_ordered * item.base_price - ABS(item.base_discount_amount) )"))

                    # invoiced
                    ->columns(array('base_row_xinvoiced'=>"(( item.qty_invoiced * (item.base_price - ABS(item.base_discount_amount) ) ) )"))
                    ->columns(array('base_tax_xinvoiced'=>"((item.qty_invoiced / item.qty_ordered) *  item.base_tax_amount)"))
                    ->columns(array('base_row_xinvoiced_incl_tax'=>"( item.qty_invoiced * (item.base_price - ABS(item.base_discount_amount) ) + (item.qty_invoiced / item.qty_ordered) *  item.base_tax_amount) "))

                    # refunded
                    ->columns(array('base_row_xrefunded'=>"(IF((item.qty_refunded > 0), 1, 0) * (  item.qty_refunded / item.qty_invoiced  * ( item.qty_invoiced * item.base_price - ABS(item.base_discount_amount) )  ))"))
                    ->columns(array('base_tax_xrefunded'=>"IF((item.qty_refunded > 0), ( item.qty_refunded / item.qty_invoiced *  item.base_tax_amount), 0)"))
                    ->columns(array('base_row_xrefunded_incl_tax'=>"(IF((item.qty_refunded > 0), 1, 0) * (  (item.qty_refunded / item.qty_invoiced  * ( item.qty_invoiced * item.base_price - ABS(item.base_discount_amount) ) + IF((item.qty_refunded > 0), ( (item.qty_refunded / item.qty_invoiced) * item.base_tax_amount), 0) )  ))"))

             ;
        return $this;
    }

    /**
     * Add manufacturer
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Manufacturer
     */
    public function addManufacturer()
    {
        $manExpr = "IFNULL(`man_val_varchar`.`value`,`man_val_int`.`value`)";
        $entityType = $this->_helper()->getSql()->getTable('eav_entity_type');
        $entityAttr = $this->_helper()->getSql()->getTable('eav_attribute');
        $entityValueVarchar = $this->_helper()->getSql()->getTable('catalog_product_entity_varchar');
        $entityValueInt = $this->_helper()->getSql()->getTable('catalog_product_entity_int');
        $this->getSelect()
            ->join(array('man_ent_type'=>$entityType), "man_ent_type.entity_type_code = 'catalog_product'", array())
            ->join(array('man_attr'=>$entityAttr), "man_attr.entity_type_id = man_ent_type.entity_type_id AND man_attr.attribute_code = 'manufacturer'", array())
            ->joinLeft(array('man_val_varchar'=>$entityValueVarchar), "man_attr.attribute_id = man_val_varchar.attribute_id AND man_val_varchar.entity_id = item.product_id AND man_val_varchar.store_id = '0'", array())
            ->joinLeft(array('man_val_int'=>$entityValueInt), "man_attr.attribute_id = man_val_int.attribute_id AND man_val_int.entity_id = item.product_id AND man_val_int.store_id = '0'", array('manufacturer'=>$manExpr))
        ;
        return $this;
    }
    

}
