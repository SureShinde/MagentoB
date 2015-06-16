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
/**
 * Sales by Manufacturer Report Grid
 */
class AW_Advancedreports_Block_Additional_Manufacturer_Grid extends AW_Advancedreports_Block_Additional_Grid
{
    protected $_routeOption = AW_Advancedreports_Helper_Additional_Manufacturer::ROUTE_ADDITIONAL_MANUFACTURER;
    protected $_optCollection;
    protected $_optCache = array();

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( $this->_helper()->getGridTemplate() );
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridAdditionalManufacturer');
    }

    public function hasRecords()
    {
        return false;
    }

    public function getHideShowBy()
    {
        return true;
    }

    public function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var AW_Advancedreports_Model_Mysql4_Collection_Additional_Manufacturer $collection  */
        $collection = Mage::getResourceModel('advancedreports/collection_additional_manufacturer');
        $collection->reInitSelect();

        $this->setCollection($collection);

        $date_from = $this->_getMysqlFromFormat($this->getFilter('report_from'));
        $date_to = $this->_getMysqlToFormat($this->getFilter('report_to'));

        $collection->setDateFilter($date_from, $date_to)
                   ->setState();
        $storeIds = $this->getStoreIds();
        if (count($storeIds)){
            $collection->setStoreFilter($storeIds);
        }
        
        $collection->addOrderItems()
                   ->addManufacturer();
        
        $this->_prepareData();
    }

    protected function _addOptionToCache($id, $value)
    {
        $this->_optCache[$id] = $value;
    }

    protected function _optionInCache($id)
    {
        if (count($this->_optCache)){
            foreach ($this->_optCache as $key=>$value){
                if ($key == $id){
                    return $value;
                }
            }
        }
    }

    protected function _getManufacturer( $option_id )
    {
        if (!$this->_optCollection)
        {
            $this->_optCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setStoreFilter(0, false)
                ->load();
        }
        # seach in quick cache
        if ($val = $this->_optionInCache($option_id)){
            return $val;
        }
        # search in chached collection
        foreach ($this->_optCollection as $item)
        {
            if ( $option_id == $item->getOptionId() ){
                $this->_addOptionToCache($option_id, $item->getValue());
                return $item->getValue();
            }  
        }
        return null;
    }

    protected function _addCustomData($row)
    {
        if ( count( $this->_customData ) )
        {
            foreach ( $this->_customData as &$d )
            {
                if ( $d['title'] == $row['title'] )
                {
                    # Qty
                    $qty_ordered = $d['qty_ordered'] + $row['qty_ordered'];
                    $d['qty_ordered'] = $qty_ordered;
                    
                    # Subtotal
                    $base_row_subtotal = $d['base_row_subtotal'] + $row['base_row_subtotal'];
                    $d['base_row_subtotal'] = $base_row_subtotal;

                    # Tax
                    $base_tax_xamount = $d['base_tax_xamount'] + $row['base_tax_xamount'];
                    $d['base_tax_xamount'] = $base_tax_xamount;

                    # Discounts
                    $base_discount_amount = $d['base_discount_amount'] + $row['base_discount_amount'];
                    $d['base_discount_amount'] = $base_discount_amount;

                    # Total
                    $base_row_xtotal = $d['base_row_xtotal'] + $row['base_row_xtotal'];
                    $d['base_row_xtotal'] = $base_row_xtotal;

                    # Total Incl. Tax
                    $base_row_xtotal_incl_tax = $d['base_row_xtotal_incl_tax'] + $row['base_row_xtotal_incl_tax'];
                    $d['base_row_xtotal_incl_tax'] = $base_row_xtotal_incl_tax;

                    # Invoiced
                    $base_row_xinvoiced = $d['base_row_xinvoiced'] + $row['base_row_xinvoiced'];
                    $d['base_row_xinvoiced'] = $base_row_xinvoiced;

                    # Tax Invoiced
                    $base_tax_xinvoiced = $d['base_tax_xinvoiced'] + $row['base_tax_xinvoiced'];
                    $d['base_tax_xinvoiced'] = $base_tax_xinvoiced;

                    # Invoiced Incl. Tax
                    $base_row_xinvoiced_incl_tax = $d['base_row_xinvoiced_incl_tax'] + $row['base_row_xinvoiced_incl_tax'];
                    $d['base_row_xinvoiced_incl_tax'] = $base_row_xinvoiced_incl_tax;

                    # Refunded
                    $base_row_refunded = $d['base_row_xrefunded'] + $row['base_row_xrefunded'];
                    $d['base_row_xrefunded'] = $base_row_refunded;

                    # Tax Refunded
                    $base_tax_refunded = $d['base_tax_xrefunded'] + $row['base_tax_xrefunded'];
                    $d['base_tax_xrefunded'] = $base_tax_refunded;

                    # Refunded Incl. Tax
                    $base_row_xrefunded_incl_tax = $d['base_row_xrefunded_incl_tax'] + $row['base_row_xrefunded_incl_tax'];
                    $d['base_row_xrefunded_incl_tax'] = $base_row_xrefunded_incl_tax;

                    
                    return ;
                }
            }
        }
        $this->_customData[] = $row;
        return $this;
    }

    protected function _prepareData()
    {
//        echo $this->getCollection()->getSelect()->__toString();
        Varien_Profiler::start('aw::advancedreports::manufacturer::prepare_data');
        foreach ($this->getCollection() as $row)
        {
            $opt_id = $row->getManufacturer();
            if ($opt_id){
                $row->setProductManufacturer( $this->_getManufacturer($opt_id) );
            } else {
                $row->setProductManufacturer( $this->_helper()->__('Not set') );
            }
            $row->setTitle( $row->getProductManufacturer() );

            $this->_addCustomData($row->getData());
        }
        parent::_prepareData();
        Varien_Profiler::stop('aw::advancedreports::manufacturer::prepare_data');
        return $this;
    }

    protected function _prepareColumns()
    {
        $def_value = sprintf("%f", 0);
        $def_value = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($def_value);

        $this->addColumn('product_manufacturer', array(
            'header'    =>$this->_helper()->__('Manufacturer'),
            'index'     =>'product_manufacturer',
            'type'      =>'text',
            'width'     =>'100px',
        ));

        $this->addColumn('qty_ordered', array(
            'header'    =>$this->_helper()->__('Quantity'),
            'width'     =>'60px',
            'index'     =>'qty_ordered',
            'total'     =>'sum',
            'type'      =>'number'
        ));

        $this->addColumn('base_row_subtotal', array(
            'header'    =>$this->_helper()->__('Subtotal'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_subtotal',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_discount_amount', array(
            'header'    =>$this->_helper()->__('Discounts'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_discount_amount',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_row_xtotal', array(
            'header'    =>$this->_helper()->__('Total'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xtotal',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_tax_xamount', array(
            'header'    =>$this->_helper()->__('Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_tax_xamount',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_row_xtotal_incl_tax', array(
            'header'    =>$this->_helper()->__('Total Incl. Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xtotal_incl_tax',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_row_xinvoiced', array(
            'header'    =>$this->_helper()->__('Invoiced'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xinvoiced',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_tax_xinvoiced', array(
            'header'    =>$this->_helper()->__('Tax Invoiced'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_tax_xinvoiced',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_row_xinvoiced_incl_tax', array(
            'header'    =>$this->_helper()->__('Invoiced Incl. Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xinvoiced_incl_tax',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_row_xrefunded', array(
            'header'    =>$this->_helper()->__('Refunded'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xrefunded',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_tax_xrefunded', array(
            'header'    =>$this->_helper()->__('Tax Refunded'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_tax_xrefunded',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_row_xrefunded_incl_tax', array(
            'header'    =>$this->_helper()->__('Refunded Incl. Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_row_xrefunded_incl_tax',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addExportType('*/*/exportOrderedCsv/name/'.$this->_getName(), $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportOrderedExcel/name/'.$this->_getName(), $this->_helper()->__('Excel'));

        return $this;
    }

    public function getChartType()
    {
        return AW_Advancedreports_Block_Chart::CHART_TYPE_PIE3D;
    }
}