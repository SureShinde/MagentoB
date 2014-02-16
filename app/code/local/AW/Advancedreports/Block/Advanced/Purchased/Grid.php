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

/**
 * Products by Customer Report Grid
 */
class AW_Advancedreports_Block_Advanced_Purchased_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    /**
     * Route to extract config from helper
     * @var string
     */
    protected $_routeOption = AW_Advancedreports_Helper_Data::ROUTE_ADVANCED_PURCHASED;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate($this->_helper()->getGridTemplate());
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridPurchased');
    }

    /**
     * Has records to build report
     * @return boolean
     */
    public function hasRecords()
    {
        return false;
    }

    /**
     * Prepare collection of report
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var AW_Advancedreports_Model_Mysql4_Collection_Purchased $collection  */
        $collection = Mage::getResourceModel('advancedreports/collection_purchased');

        $this->setCollection($collection);
        $date_from = $this->_getMysqlFromFormat($this->getFilter('report_from'));
        $date_to = $this->_getMysqlToFormat($this->getFilter('report_to'));
        $this->getCollection()->setDateFilter($date_from, $date_to)->setState();
        $storeIds = $this->getStoreIds();
        if (count($storeIds)) {
            $collection->setStoreFilter($storeIds);
        }

        $collection->addOrderItemsCount();
        $this->_helper()->setNeedMainTableAlias(true);
        $this->_prepareData();
        return $this;
    }

    /**
     * Add data to Data cache
     * @param array $row Row of data
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    protected function _addCustomData($row)
    {
        if (count($this->_customData)) {
            foreach ($this->_customData as &$d) {
                if (isset($d['customers']) && ($d['sum_qty'] == $row['sum_qty'])) {
                    $customers = $d['customers'];
                    unset($d['customers']);
                    $d['customers'] = $customers + 1;
                    $d['x_base_total'] = $d['x_base_total'] + $row['x_base_total'];
                    $d['x_base_total_invoiced'] = $d['x_base_total_invoiced'] + $row['x_base_total_invoiced'];
                    $d['x_base_total_refunded'] = $d['x_base_total_refunded'] + $row['x_base_total_refunded'];
                    return $this;
                }
            }
        }
        $this->_customData[] = $row;
        return $this;
    }

    /**
     * Prepare data array for Pie and Grid
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    protected function _prepareData()
    {
        foreach ($this->getCollection() as $order)
        {
            $row = array();

            foreach ($this->_columns as $column)
            {
                if (!$column->getIsSystem()) {
                    $row[$column->getIndex()] = $order->getData($column->getIndex());
                }
            }
            $row['customers'] = 1;
            $row['title'] = round($row['sum_qty']);
            $this->_addCustomData($row);
        }


        if (!count($this->_customData)) {
            return $this;
        }

        usort($this->_customData, array(&$this, "_compareQtyElements"));
        $this->_helper()->setChartData($this->_customData, $this->_helper()->getDataKey($this->_routeOption));
        parent::_prepareData();
        return $this;
    }

    /**
     * Sort bestsellers values in two arrays
     * @param array $a
     * @param array $b
     * @return integer
     */
    protected function _compareQtyElements($a, $b)
    {
        if ($a['sum_qty'] == $b['sum_qty']) {
            return 0;
        }
        return ($a['sum_qty'] > $b['sum_qty']) ? -1 : 1;
    }

    /**
     * Prepare columns to show grid
     * @return AW_Advancedreports_Block_Advanced_Purchased_Grid
     */
    protected function _prepareColumns()
    {
        $currency_code = $this->getCurrentCurrencyCode();

        $this->addColumn('sum_qty', array(
            'header' => $this->_helper()->__('Products Purchased'),
            'align' => 'right',
            'index' => 'sum_qty',
            'total' => 'sum',
            'type' => 'number'
        ));

        $this->addColumn('customers', array(
            'header' => $this->_helper()->__('Number of Customers'),
            'align' => 'right',
            'index' => 'customers',
            'total' => 'sum',
            'type' => 'number'
        ));

        $this->addColumn('x_base_total', array(
            'header' => Mage::helper('reports')->__('Total'),
            'type' => 'currency',
            'currency_code' => $currency_code,
            'index' => 'x_base_total',
            'total' => 'sum',
            'renderer' => 'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addColumn('x_base_total_invoiced', array(
            'header' => Mage::helper('reports')->__('Invoiced'),
            'type' => 'currency',
            'currency_code' => $currency_code,
            'index' => 'x_base_total_invoiced',
            'total' => 'sum',
            'renderer' => 'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addColumn('x_base_total_refunded', array(
            'header' => Mage::helper('reports')->__('Refunded'),
            'type' => 'currency',
            'currency_code' => $currency_code,
            'index' => 'x_base_total_refunded',
            'total' => 'sum',
            'renderer' => 'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addExportType('*/*/exportOrderedCsv', $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel', $this->_helper()->__('Excel'));

        return $this;
    }

    /**
     * Retrives type of chart for grid
     * (need for compatibiliy wit other reports)
     * @return string
     */
    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_PIE3D;
    }
}
