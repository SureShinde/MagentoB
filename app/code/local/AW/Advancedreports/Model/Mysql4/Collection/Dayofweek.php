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
 * @package    AW_Advancedreports
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Advancedreports_Model_Mysql4_Collection_Dayofweek
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Set up day of week filter
     * $dayofweek = 1,2,3,4,5,6,7
     * @return AW_Advancedreports_Model_Mysql4_Collection_Dayofweek
     */
    public function setDayOfWeekFilter()
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $filterField = $this->_helper()->confOrderDateFilter();
        $globalTz = $this->_helper()->getTimeZoneOffset(true);

        if($filterField == "invoice_created_at"){
            $this->getSelect()
                    ->join( array('item'=>$itemTable), "main_table.entity_id = item.order_id AND item.parent_item_id IS NULL", array( 'day_of_week' => "DAYOFWEEK(CONVERT_TZ(sales_flat_order_status_history.created_at, '+00:00', '{$globalTz}'))", 'sum_qty' => 'SUM(item.qty_ordered)',  'sum_total' => 'SUM(item.base_row_total)', 'name' => 'name', 'sku'=>'sku' ) )
                    ->group('day_of_week');
        }else{
	        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
	            $this->getSelect()
	                    ->join( array('item'=>$itemTable), "main_table.entity_id = item.order_id AND item.parent_item_id IS NULL", array( 'day_of_week' => "DAYOFWEEK(CONVERT_TZ(main_table.{$filterField}, '+00:00', '{$globalTz}'))", 'sum_qty' => 'SUM(item.qty_ordered)',  'sum_total' => 'SUM(item.base_row_total)', 'name' => 'name', 'sku'=>'sku' ) )
	                    ->group('day_of_week');
	        } else {
	            $this->getSelect()
	                    ->join( array('item'=>$itemTable), "e.entity_id = item.order_id AND item.parent_item_id IS NULL", array( 'day_of_week' => "DAYOFWEEK(CONVERT_TZ(e.{$filterField}, '+00:00', '{$globalTz}'))", 'sum_qty' => 'SUM(item.qty_ordered)',  'sum_total' => 'SUM(item.base_row_total)', 'name' => 'name', 'sku'=>'sku' ) )
	                    ->group('day_of_week');
	        }
        }
        return $this;
    }

}
