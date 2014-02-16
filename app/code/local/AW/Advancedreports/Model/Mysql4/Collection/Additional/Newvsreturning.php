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
 * @package    AW_ARUnits/Newvsreturning
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Advancedreports_Model_Mysql4_Collection_Additional_Newvsreturning
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{

    /**
     * Reinitialize collection
     * @param false $useHelpSql
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Newvsreturning
     */
    public function reInitOrdersCollection($useHelpSql = false, $options = array())
    {
        /** @var string $sql Help SQL */
        $sql = null;
        if ($useHelpSql && isset($options['from']) && isset($options['to'])){
            $sql = $this->_getTotalOrderCollection($options['from'], $options['to'])->getSelect()->__toString();
            $sql = str_replace('`', '', $sql);
        }

        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
        } else {
            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        }
        $this->getSelect()->reset();

        $tableAlias = $this->_getSalesCollectionTableAlias();

        $arr =  array(
                'orders_count'     => "COUNT({$tableAlias}.entity_id)", # Just because it's unique
                'customer_id' => "{$tableAlias}.customer_id",
        );

        if ($sql){
            $arr['orders_count'] = new Zend_Db_Expr('('.$sql.')');  
        }

        $this->getSelect()->from(array($tableAlias=>$orderTable), $arr);
        $this->getSelect()->group("{$tableAlias}.customer_id");
        return $this;
    }


    /**
     * Add NULL customer filter
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Newvsreturning
     */
    public function addNullCustomer()
    {
        $tableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()->where("{$tableAlias}.customer_id IS NULL");
        return $this;
    }

    /**
     * Add not null customer
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Newvsreturning
     */
    public function addNotNullCustomer()
    {
        $customersTable = $this->_helper()->getSql()->getTable('customer_entity');
        $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        $tableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()
				//  ->join( array('customer' => $customersTable), "(customer.entity_id = {$tableAlias}.customer_id AND customer.created_at >= '{$cust_from}' AND customer.created_at <= '{$to}' AND customer.is_active = '1')", array() )
            ->join( array('customer' => $customersTable), "(customer.entity_id = {$tableAlias}.customer_id AND customer.is_active = '1')", array('customer_created_at' => 'created_at') )
            ;
        return $this;
    }


    /**
     * Retrives collection with all orders
     *
     * @param string|datetime $from
     * @param string|datetime $to
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected function _getTotalOrderCollection($from, $to)
    {
        $collection = Mage::getModel('advancedreports/order')->getCollection();
        
        $filterField = $this->_helper()->confOrderDateFilter();
        if($filterField == "invoice_created_at"){
        	$orderTable = $this->getTable('sales_flat_order_status_history');

        	$collection->getSelect()->reset();
        	$collection->getSelect()->from(array('ee'=>$orderTable), array(
        			"COUNT(DISTINCT(ee.parent_id))", # Because it's unique
        	));
        	$collection->getSelect()->where("ee.created_at <= ?", $to);
        }else{
	        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
	            $orderTable = $this->getTable('sales_flat_order');
	        } else {
	            $orderTable = $this->getTable('sales_order');
	        }
	
	        $collection->getSelect()->reset();
	        $collection->getSelect()->from(array('ee'=>$orderTable), array(
	                "COUNT(ee.entity_id)", # Because it's unique
	            ));
	    	if($filterField == "invoice_created_at") $filterField = "created_at";
	        $collection->getSelect()
	                            ->where("ee.{$filterField} <= ?", $to);
        }

        # It's a very bad style but in this case have no time to do it clear
        # TODO Do it clear in future
        if ($this->_helper()->checkSalesVersion('1.4.0.0')){
            $collection->getSelect()
                    ->where($this->_getEEProcessStates())
                    ;
        } elseif ($this->_checkSVer('0.9.56')){
            $entity = $this->getTable('sales_order_entity');
            $entityValues = $this->getTable('sales_order_entity_varchar');
            $entityAtribute = $this->getTable('eav_attribute');
            $collection->getSelect()
                    ->join( array('attr1'=>$entityAtribute), "attr1.attribute_code = 'status'", array())
                    ->join( array('ent1'=>$entity), "ent1.parent_id = ee.entity_id", array())
                    ->join( array('val1'=>$entityValues), "attr1.attribute_id = val1.attribute_id AND ".$this->_getXProcessStates()." AND ent1.entity_id = val1.entity_id", array())
                    ;
        } else {
            $entityValues = $this->getTable('sales_order_varchar');
            $entityAtribute = $this->getTable('eav_attribute');
            $collection->getSelect()
                    ->join( array('attr1'=>$entityAtribute), "attr1.attribute_code = 'status'", array())
                    ->join( array('val1'=>$entityValues), "attr1.attribute_id = val1.attribute_id AND ".$this->_getXProcessStates()." AND ee.entity_id = val1.entity_id", array())
                    ;
        }

        $collection->getSelect()
            ->group('ee.customer_id')
            ->where('ee.customer_id = e.customer_id')
            ;

        # check Store Filter
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }

        if (isset($storeIds))
        {
            $collection->getSelect()
                    ->where("ee.store_id in ('".implode("','", $storeIds)."')");
        }
        return $collection;
    }


    /**
     * Retrives filter statuses for SQL query
     * (1.4.1.0 and Enterprise)
     * @return string
     */
    protected function _getEEProcessStates()
    {
        $states = explode( ",", $this->_helper()->confProcessOrders() );
        $is_first = true;
        $filter = "";
        foreach ($states as $state)
        {
            if (!$is_first)
            {
            $filter .= " OR ";
            }
            $filter .= "ee.status = '".$state."'";
            $is_first = false;
        }
        return "(".$filter.")";
    }

    /**
     * Retrives filter statuses for SQL query
     * (Old community)
     * @return string
     */
    protected function _getXProcessStates()
    {
        $states = explode( ",", $this->_helper()->confProcessOrders() );
        $is_first = true;
        $filter = "";
        foreach ($states as $state)
        {
            if (!$is_first)
            {
            $filter .= " OR ";
            }
            $filter .= "val1.value = '".$state."'";
            $is_first = false;
        }
        return "(".$filter.")";
    }

    /**
     * Retrives filter statuses for SQL query
     * (1.4.0.0)
     * @return string
     */
    protected function _get1400ProcessStates()
    {
        $states = explode( ",", $this->_helper()->confProcessOrders() );
        $is_first = true;
        $filter = "";
        foreach ($states as $state)
        {
            if (!$is_first)
            {
                $filter .= " OR ";
            }
            $filter .= "ee.state = '".$state."'";
            $is_first = false;
        }
        return "(".$filter.")";
    }

}
