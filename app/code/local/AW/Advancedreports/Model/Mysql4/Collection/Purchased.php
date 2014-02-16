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

class AW_Advancedreports_Model_Mysql4_Collection_Purchased
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Add order query to collection select
     * @return AW_Advancedreports_Model_Mysql4_Collection_Purchased
     */
    public function addOrderItemsCount()
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
            $this->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = main_table.entity_id AND item.parent_item_id IS NULL)", array( 'sum_qty' => 'SUM(item.qty_ordered)'))
                    ->where("main_table.entity_id = item.order_id")
                    ->group('main_table.entity_id')
                    ;
        } else {
            $this->getSelect()
                    ->join( array('item'=>$itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)", array( 'sum_qty' => 'SUM(item.qty_ordered)'))
                    ->where("e.entity_id = item.order_id")
                    ->group('e.entity_id')
                    ;
        }
        $this->getSelect()
                ->columns(array(
                        'x_base_total' => 'base_grand_total',
                        'x_base_total_invoiced' => 'base_total_invoiced',
                        'x_base_total_refunded' => 'base_total_refunded',
                ));
        return $this;
    }


}