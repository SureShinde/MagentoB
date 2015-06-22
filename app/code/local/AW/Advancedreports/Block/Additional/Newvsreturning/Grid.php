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
/**
 * New vs Returning Customers Report Grid
 */
class AW_Advancedreports_Block_Additional_Newvsreturning_Grid extends AW_Advancedreports_Block_Additional_Grid
{
    /**
     * Route to access in session to chart params
     * @var string
     */
    protected $_routeOption = AW_Advancedreports_Helper_Additional_Newvsreturning::ROUTE_ADDITIONAL_NEWVSRETURNING;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( $this->_helper()->getGridTemplate() );
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridAdditionalNewvsreturning');
    }

    /**
     * Flag to hide Show By block
     * @return boolean
     */
    public function getHideShowBy()
    {
        return false;
    }

    /**
     * Prepare collection to use in grid
     * @return AW_Advancedreports_Block_Additional_Newvsreturning_Grid
     */
    public function _prepareCollection()
    {
        parent::_prepareOlderCollection();
        # This calculate collection of intervals
        $this->getCollection()
            ->initReport('reports/product_ordered_collection');
        $this->_prepareData();
        return $this;
    }

    /**
     * Retrives report data for period
     *
     * @param string $from
     * @param string $to
     * @return Varien_Object
     */
    protected function _getInfo($from, $to)
    {
        # Get global period for sort customers
        $global_from     = $this->_getMysqlFromFormat($this->getFilter('report_from'));
        $global_to         = $this->_getMysqlFromFormat($this->getFilter('report_to'));

        $result = new Varien_Object(array( 'new_customers' => 0, 'returning_customers' => 0 ));
        if ($new = $this->_getNewCutomers($from, $to)){
            $result->setNewCustomers($result->getNewCustomers() +  $new->getNewCustomers() );
            $result->setReturningCustomers($result->getReturningCustomers() + $new->getReturningCustomers() );
        }
        if ($returning =  $this->_getReturningCutomers($from, $to , $global_from, $global_to) ){
            $result->setNewCustomers($result->getNewCustomers() +  $returning->getNewCustomers() );
            $result->setReturningCustomers($result->getReturningCustomers() + $returning->getReturningCustomers() );
        }
        return $result;
    }

    /**
     * Retrives new customers count for period
     * @param string|datetime $from
     * @param string|datetime $to
     * @return Varien_Object
     */
    protected function _getNewCutomers($from, $to)
    {
        $collection = $this->_getOrdersCollection($from, $to);
        $collection->addNullCustomer();

        if (count($collection)){
            foreach ($collection as $item){
                return new Varien_Object(array( 'new_customers' => $item->getOrdersCount(), 'returning_customers' => 0 ));
            }
        }
        return new Varien_Object(array( 'new_customers' => 0, 'returning_customers' => 0 ));
    }



    /**
     * Retrives returning customers count for period
     * @param string|datetime $from
     * @param string|datetime $to
     * @return Varien_Object
     */
    protected function _getReturningCutomers($from, $to, $cust_from = null, $cust_to = null)
    {
        if ($cust_from && $cust_to){
            $collection = $this->_getOrdersCollection($from, $to, true, array(
                                                                        'from' => $cust_from,
                                                                        'to'   => $to
                                                                       ));

            $collection->addNotNullCustomer();
        }

        $result = new Varien_Object(array( 'new_customers' => 0, 'returning_customers' => 0 ));

        if (count($collection)){
            foreach ($collection as $item){
                if ($item->getOrdersCount() && $this->_is_day_in_period($from, $to, $item->getCustomerCreatedAt())){
                    $result->setNewCustomers( $result->getNewCustomers() + 1 );
                } elseif ($item->getOrdersCount() > 1){
                     $result->setReturningCustomers( $result->getReturningCustomers() + 1 );
                 } else {
                     $result->setNewCustomers( $result->getNewCustomers() + 1 );
                 }
            }
        }
        return $result;
    }

    /**
     * Save data row to use in chart
     * @param array $row
     * @return AW_Advancedreports_Block_Additional_Newvsreturning_Grid
     */ 
    protected function _addCustomData($row)
    {
        $this->_customData[] = $row;
        return $this;
    }


    /**
     * Retrives collection with orders count
     *
     * @param string|datetime $from
     * @param string|datetime $to
     * @param boolean $useHelpSql Sql that helps to extract all orders count for period for customer
     * @return AW_Advancedreports_Model_Mysql4_Collection_Additional_Newvsreturning
     */
    protected function _getOrdersCollection($from, $to, $useHelpSql = false)
    {
        /** @var AW_Advancedreports_Model_Mysql4_Collection_Additional_Newvsreturning $collection  */
        $collection = Mage::getResourceModel('advancedreports/collection_additional_newvsreturning');

        $collection->reInitOrdersCollection($useHelpSql);

        $collection->setDateFilter($from, $to)
                    ->setState();

        $storeIds = $this->getStoreIds();
        if (count($storeIds)){
            $collection->setStoreFilter($storeIds);
        }
        return $collection;
    }



    /**
     * Retrives is day in period
     * @param string|datetime $from
     * @param string|datetime $to
     * @param string|datetime $day
     * @return boolean
     */
    protected function _is_day_in_period($from, $to, $day){
        return ($from <= $day && $day <= $to);
    }



    protected function _prepareData()
    {
        # primary analise
        foreach ( $this->getCollection()->getIntervals() as $_index=>$_item )
        {
            $row = $this->_getInfo($_item['start'], $_item['end']);
            $row->setPeriod($_item['title']);

            $this->_addCustomData($row->getData());
        }

        $chartLabels = array('new_customers' => $this->_helper()->__('New Customers'),
                             'returning_customers'    => $this->_helper()->__('Returning Customers') );
        $keys = array();
        foreach ($chartLabels as $key=>$value)
        {
            $keys[] = $key;
        }

        foreach ( $this->_customData as $i=>&$d )
        {
            $total = $d['new_customers'] + $d['returning_customers'];
            if ($total > 0){
                $d['percent_of_new'] = round( $d['new_customers'] * 100 / $total ).' %';
                $d['percent_of_new_data'] = round( $d['new_customers'] * 100 / $total );
                $d['percent_of_returning'] = round( $d['returning_customers'] * 100 / $total ).' %';
                $d['percent_of_returning_data'] = round( $d['returning_customers'] * 100 / $total );
            } else {
                $d['percent_of_new'] = '0 %';
                $d['percent_of_new_data'] = 0;
                $d['percent_of_returning'] = '0 %';
                $d['percent_of_returning_data'] = 0;
            }

        }

        $this->_helper()->setChartData( $this->_customData, $this->_helper()->getDataKey( $this->_routeOption ) );
        $this->_helper()->setChartKeys( $keys, $this->_helper()->getDataKey( $this->_routeOption )  );
        $this->_helper()->setChartLabels( $chartLabels, $this->_helper()->getDataKey( $this->_routeOption )  );
        parent::_prepareData();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('periods', array(
            'header'    =>$this->getPeriodText(),
            'width'     =>'120px',
            'index'     =>'period',
            'sortable'  => false,
            'type'      =>'text'
        ));

        $this->addColumn('new_customers', array(
            'header' => $this->_helper()->__('New Customers'),
            'index' => 'new_customers',
            'type' => 'number',
            'width' => '80px',
        ));

        $this->addColumn('returning_customers', array(
            'header' => $this->_helper()->__('Returning Customers'),
            'index' => 'returning_customers',
            'type' => 'number',
            'width' => '80px',
        ));

        $this->addColumn('percent_of_new', array(
            'header'    =>$this->_helper()->__('Percent of New'),
            'width'     =>'80px',
            'align'     =>'right',
            'index'     =>'percent_of_new',
            'type'      =>'text',
        ));

        $this->addColumn('percent_of_returning', array(
            'header'    =>$this->_helper()->__('Percent of Returning'),
            'width'     =>'80px',
            'align'     =>'right',
            'index'     =>'percent_of_returning',
            'type'      =>'text',
        ));

        $this->addExportType('*/*/exportOrderedCsv/name/'.$this->_getName(), $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel/name/'.$this->_getName(), $this->_helper()->__('Excel'));

        return $this;
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_MULTY_LINE;
    }

    public function getPeriods()
    {
        return parent::_getOlderPeriods();
    }
}