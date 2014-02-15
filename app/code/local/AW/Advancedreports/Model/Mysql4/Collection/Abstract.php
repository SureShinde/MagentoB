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

class AW_Advancedreports_Model_Mysql4_Collection_Abstract extends AW_Advancedreports_Model_Mysql4_Order_Collection
{
    /**
     * Not use inside all whose wrappers
     * Notice: Use $this->_helper()->getSql()->getTable($table) instead
     *
     * @deprecated
     * @param $table
     * @return string
     */
    public function getTable($table)
    {
        return parent::getTable($table);
    }

    /**
     * Retrieves helper
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    /**
     * Set up date filter to collection of grid
     *
     * @param Datetime $from
     * @param Datetime $to
     * @return AW_Advancedreports_Model_Mysql4_Collection_Abstract
     */
    public function setDateFilter($from, $to)
    {
        $filterField = $this->_helper()->confOrderDateFilter();
    	if($filterField == "invoice_created_at"){
            $salesHistoryAtribute = $this->_helper()->getSql()->getTable('sales_flat_order_status_history');
    		$this->getSelect()
	    		->joinLeft(array('sales_flat_order_status_history' => $salesHistoryAtribute), "sales_flat_order_status_history.parent_id = main_table.entity_id AND sales_flat_order_status_history.status = 'processing' AND sales_flat_order_status_history.entity_name = 'invoice'", array());
    		
    		$this->getSelect()
	    		->where("sales_flat_order_status_history.created_at >= ?", $from)
	    		->where("sales_flat_order_status_history.created_at <= ?", $to);
    	}else{
    		if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
    			$this->getSelect()
	    			->where("main_table.{$filterField} >= ?", $from)
	    			->where("main_table.{$filterField} <= ?", $to);
    		} else {
    			$this->getSelect()
	    			->where("e.{$filterField} >= ?", $from)
	    			->where("e.{$filterField} <= ?", $to);
    		}
    	}
        return $this;
    }


    /**
     * Set up order state filter
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Abstract
     */
    public function setState()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->addAttributeToFilter('main_table.status', explode(",", $this->_helper()->confProcessOrders()));
        } else {
            $entityValues = $this->_helper()->getSql()->getTable('sales_order_varchar');
            $entityAtribute = $this->_helper()->getSql()->getTable('eav_attribute');
            $this->getSelect()
                ->join(array('attr' => $entityAtribute), "attr.attribute_code = 'status'", array())
                ->join(array('val' => $entityValues), "attr.attribute_id = val.attribute_id AND " . $this->_helper()->getSql()->getProcessStates() . " AND e.entity_id = val.entity_id", array());
        }
        return $this;
    }

    /**
     * Sales Collection Table Alias
     *
     * @return string
     */
    protected function _getSalesCollectionTableAlias()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            return 'main_table';
        } else {
            return 'e';
        }
    }

}
