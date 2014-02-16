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

class AW_Advancedreports_Model_Mysql4_Collection_Product
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{

    /**
     * Reinitialize select
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Product
     */
    public function reInitSelect()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
        } else {
            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        }
        $this->getSelect()->reset();
        $this->getSelect()->from(array($this->_getSalesCollectionTableAlias() => $orderTable), array());
        return $this;
    }

    public function setSkusFilter($skus = array())
    {
        if ($filter = $this->_getWhereSkusFilter($skus)){
            $this->getSelect()->where("({$filter})");
        }
        return $this;
    }
    
    /**
     * Retrieves SQL filter string
     *
     * @param array $skus
     * @return null|string
     */
    protected function _getWhereSkusFilter($skus = array())
    {
        if ( count($skus) ){
            $filter = '';
            $is_first = true;
            foreach($skus as $sku)
            {
                if (!$is_first){
                    $filter .= ' OR ';
                }
                $filter .= "item.sku = '{$sku}'";
                $is_first = false;
            }
            return $filter;
        }
        return null;
    }

    /**
     * Add items
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Product
     */
    public function addItems()
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $orderTableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()
                ->join( array('item'=>$itemTable), "{$orderTableAlias}.entity_id = item.order_id AND item.parent_item_id IS NULL", array( 'sum_qty' => 'SUM(item.qty_ordered)',  'sum_total' => 'SUM(item.base_row_total - item.base_discount_amount + item.base_tax_amount)', 'name' => 'name', 'sku'=>'sku' ) )
                ->group('item.sku');
        return $this;
    }

}