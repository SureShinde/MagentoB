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
/**
 * Sales by Payment Type Report Grid
 */
class AW_Advancedreports_Block_Additional_Salesbypaymenttype_Grid extends AW_Advancedreports_Block_Additional_Grid
{
    protected $_routeOption = AW_Advancedreports_Helper_Additional_Salesbypaymenttype::ROUTE_ADDITIONAL_SALESBYPAYMENTTYPE;
    protected $_methodCache = array();
    protected $_methodExcludes = array(
        'googlecheckout' => 'Google Checkout',

    );

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( $this->_helper()->getGridTemplate() );
        $this->setExportVisibility(true);
        $this->setStoreSwitcherVisibility(true);
        $this->setId('gridAdditionalSalesbypaymenttype');
    }

    public function _prepareCollection()
    {
        parent::_prepareCollection();

        /** @var AW_Advancedreports_Model_Mysql4_Collection_Additional_Salesbypaymenttype $collection  */
        $collection = Mage::getResourceModel('advancedreports/collection_additional_salesbypaymenttype');
		
        //$collection->printlogquery(true);

        $this->setCollection( $collection );
        $this->_prepareAbstractCollection();

        $collection->addOrderItemsCount()->addPaymentMethod();
        $this->_prepareData();
    }

    public function hasRecords()
    {
        return false;
    }

    public function getHideShowBy()
    {
        return true;
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
                    $base_subtotal = $d['base_subtotal'] + $row['base_subtotal'];
                    $d['base_subtotal'] = $base_subtotal;
                    
                    # Shipping
                    $base_shipping_amount = $d['base_shipping_amount'] + $row['base_shipping_amount'];
                    $d['base_shipping_amount'] = $base_shipping_amount;

                    # Tax
                    $base_tax_amount = $d['base_tax_amount'] + $row['base_tax_amount'];
                    $d['base_tax_amount'] = $base_tax_amount;

                    # Discounts
                    $base_discount_amount = $d['base_discount_amount'] + $row['base_discount_amount'];
                    $d['base_discount_amount'] = $base_discount_amount;

                    # Total
                    $base_grand_total = $d['base_grand_total'] + $row['base_grand_total'];
                    $d['base_grand_total'] = $base_grand_total;

                    # Invoiced
                    $base_total_invoiced = $d['base_total_invoiced'] + $row['base_total_invoiced'];
                    $d['base_total_invoiced'] = $base_total_invoiced;

                    # Refunded
                    $base_total_refunded = $d['base_total_refunded'] + $row['base_total_refunded'];
                    $d['base_total_refunded'] = $base_total_refunded;
                    return ;
                }
            }
        }
        $this->_customData[] = $row;
        return $this;
    }

    protected function _getMethodInstance($code)
    {
        $key = Mage_Payment_Helper_Data::XML_PATH_PAYMENT_METHODS.'/'.$code.'/model';
        $class = Mage::getStoreConfig($key);
        if (!$class) {
            return false;
        }
        return Mage::getSingleton($class);
    }

    protected function _getMethodTitle($code)
    {
        if (!isset( $this->_methodCache[$code] )){
            if (isset($this->_methodExcludes[$code])){
                $this->_methodCache[$code] = Mage::helper('payment')->__($this->_methodExcludes[$code]);
            } elseif ($method = $this->_getMethodInstance($code)){
                $this->_methodCache[$code] = $method->getTitle();
            } else {
                $this->_methodCache[$code] = '';
            }
        }
        if (!$this->_methodCache[$code]){
            $this->_methodCache[$code] = $code;
        }
        return $this->_methodCache[$code];
    }

    protected function _prepareData()
    {
        Varien_Profiler::start('aw::advancedreports::salesbypaymenttype::prepare_data');
        foreach ($this->getCollection() as $row)
        {
            if ($row->getMethod()){
                $row->setPaymentType( $this->_getMethodTitle( $row->getMethod() ) );
            } else {
                $row->setPaymentType( $this->_helper()->__('Not set') );
            }
            $row->setTitle( $row->getPaymentType() );

            if ($d = $row->getBaseDiscountAmount()){
                $row->setBaseDiscountAmount(abs($d));
            }

            $this->_addCustomData($row->getData());
        }
        Varien_Profiler::stop('aw::advancedreports::salesbypaymenttype::prepare_data');
        parent::_prepareData();
        return $this;
    }

    protected function _prepareColumns()
    {
        $def_value = sprintf("%f", 0);
        $def_value = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($def_value);

        $this->addColumn('payment_type', array(
            'header'    =>$this->_helper()->__('Payment Type'),
            'index'     =>'payment_type',
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

        $this->addColumn('base_subtotal', array(
            'header'    =>$this->_helper()->__('Subtotal'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_subtotal',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_shipping_amount', array(
            'header'    =>$this->_helper()->__('Shipping'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_shipping_amount',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_tax_amount', array(
            'header'    =>$this->_helper()->__('Tax'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_tax_amount',
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

        $this->addColumn('base_grand_total', array(
            'header'    =>$this->_helper()->__('Total'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_grand_total',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_total_invoiced', array(
            'header'    =>$this->_helper()->__('Invoiced'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_total_invoiced',
            'column_css_class' => 'nowrap',
            'default'  => $def_value,
        ));

        $this->addColumn('base_total_refunded', array(
            'header'    =>$this->_helper()->__('Refunded'),
            'width'     =>'80px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total'     =>'sum',
            'index'     =>'base_total_refunded',
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
