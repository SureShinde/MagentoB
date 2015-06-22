<?php

/**
 * Adminhtml coupons report grid block
 *
 * @category   Bilna
 * @package    Bilna_Customreports
 * @author     Bilna Development Team <development@bilna.com>
 */

class Bilna_Customreports_Block_Adminhtml_Salesreport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId("salesreportGrid");
		$this->setDefaultSort("created_at");
		$this->setDefaultDir("ASC");
		$this->setFilterVisibility(false);
		$this->setUseAjax(false);
// 		$this->setSaveParametersInSession(true);
	}

    /**
     * Retrieve correct currency code for select website, store, group
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        if (is_null($this->_currentCurrencyCode)) {
            if ($this->getRequest()->getParam('store')) {
                $store = $this->getRequest()->getParam('store');
                $this->_currentCurrencyCode = Mage::app()->getStore($store)->getBaseCurrencyCode();
            } else if ($this->getRequest()->getParam('website')){
                $website = $this->getRequest()->getParam('website');
                $this->_currentCurrencyCode = Mage::app()->getWebsite($website)->getBaseCurrencyCode();
            } else if ($this->getRequest()->getParam('group')){
                $group = $this->getRequest()->getParam('group');
                $this->_currentCurrencyCode =  Mage::app()->getGroup($group)->getWebsite()->getBaseCurrencyCode();
            } else {
                $this->_currentCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
            }
        }
        return $this->_currentCurrencyCode;
    }

	protected function _prepareCollection()
	{
		$collection = Mage::getModel("customreports/salesreport")->getCollection();

		$filter = $this->getRequest()->getParam('filter');
		parse_str(urldecode(base64_decode($filter)), $params);
		
        if( isset($params['from']) && isset($params['to']) )
        {
        	$from = date('Y-m-d', strtotime(str_replace('-', '/', $params['from'])));
        	$to   = date('Y-m-d', strtotime(str_replace('-', '/', $params['to'])));         	
        }else{
        	$from = date('Y-m-d');
        	$to   = date('Y-m-d');   
        }

        if( isset($params['report_type']) )
        {
   			$report_type = $params['report_type'];
        }else{
   			$report_type = "order_created_at";
        }
        
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
	    $collection	->getSelect()
	     			->columns(array(
	    					"order_increment_id",
	       					'created_at' => "order_created_at",
	       					"xsku",
	       					"customer_email",
	       					"customer_group",
	       					"order_ship_country_id",
	       					"order_ship_region",
	       					"order_ship_city",
	       					"order_ship_postcode",
	       					"name",
	       					"brand",
	       					"supplier",
	       					"supplier_neccessity",
	       					"xqty_ordered",
	       					"xqty_invoiced",
	       					"xqty_shipped",
	       					"xqty_refunded",
	       					"base_xprice",
	       					"base_row_subtotal",
	       					"base_tax_amount",
	       					"base_discount_amount",
	       					"points_to_money",
	       					"base_tax_amount",
	       					"base_row_xtotal",
	       					"base_row_xtotal_incl_tax",
	       					"base_row_xinvoiced",
	       					"base_tax_invoiced",
	       					"base_row_xinvoiced_incl_tax",
	       					"base_row_xrefunded",
	       					"base_tax_xrefunded",
	       					"base_row_xrefunded_incl_tax",
	       					"xorder_id",
	       					"product_id"
	       					));
		$collection
			->addFieldToFilter("DATE($report_type)", array ('gteq' => $from));
		$collection
			->addFieldToFilter("DATE($report_type)", array ('lteq' => $to));   
        
        if( isset($params['show_order_statuses']) && $params['show_order_statuses'] ==1 )
        {
        	$collection
				->addFieldToFilter("main_table.status", array (
					array(
						'attribute' => 'status',
						'in'		=> 	explode(",",$params['order_statuses'][0])
					)
				));
        }

        if( isset($params['include_refunded_items']) && $params['include_refunded_items'] == "false" ){
            $collection->getSelect()->where('? > 0', new Zend_Db_Expr('(xqty_ordered - xqty_refunded)'));
        }
//         echo $collection->getSelect();die;

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
        $def_value = sprintf("%f", 0);
        $def_value = Mage::app()->getLocale()->currency($this->getCurrentCurrencyCode())->toCurrency($def_value);
        
        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('customreports')->__('Order #'),
            'index' => 'order_increment_id',
            'type' => 'text',
            'width' => '80px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));
        
		$this->addColumn('created_at', array(
            'header'            => Mage::helper('customreports')->__('Order Date'),
            'index'             => 'created_at',
            'width'             => 100,
            'period_type'       => $this->getPeriodType(),
            'filter_index' 		=> 'created_at',
            'type'				=> 'date'
        ));
		
        $this->addColumn('xsku', array(
            'header' => Mage::helper('customreports')->__('SKU'),
            'width' => '120px',
            'index' => 'xsku',
            'type' => 'text',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => false),
        ));

        $this->addColumn('customer_email', array(
            'header' => Mage::helper('customreports')->__('Customer Email'),
            'index' => 'customer_email',
            'type' => 'text',
            'width' => '100px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('customer_group', array(
            'header' => Mage::helper('customreports')->__('Customer Group'),
            'index' => 'customer_group',
            'type' => 'text',
            'width' => '100px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('order_ship_country_id', array(
            'header' => Mage::helper('customreports')->__('Country'),
            'index' => 'order_ship_country_id',
            'type' => 'country',
            'width' => '100px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 10,
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('order_ship_region', array(
            'header' => Mage::helper('customreports')->__('Region'),
            'index' => 'order_ship_region',
            'type' => 'text',
            'width' => '100px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('order_ship_city', array(
            'header' => Mage::helper('customreports')->__('City'),
            'index' => 'order_ship_city',
            'type' => 'text',
            'width' => '100px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('order_ship_postcode', array(
            'header' => Mage::helper('customreports')->__('Zip Code'),
            'index' => 'order_ship_postcode',
            'type' => 'text',
            'width' => '60px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('customreports')->__('Product Name'),
            'index' => 'name',
            'type' => 'text',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));
		
        $this->addColumn('brand', array(
            'header' => Mage::helper('customreports')->__('Brand'),
            'index' => 'brand',
            'type' => 'text',
            'width' => '100px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));
        
        $this->addColumn('supplier', array(
            'header' => Mage::helper('customreports')->__('Supplier'),
            'index' => 'supplier',
            'type' => 'text',
            'width' => '100px',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'ddl_size' => 255,
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('supplier_neccessity', array(
        		'header' => Mage::helper('customreports')->__('Supplier Neccessity'),
        		'index' => 'supplier_neccessity',
        		'type' => 'text',
        		'width' => '100px',
        		'ddl_type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
        		'ddl_size' => 255,
        		'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('xqty_ordered', array(
            'header' => Mage::helper('customreports')->__('Qty. Ordered'),
            'width' => '60px',
            'index' => 'xqty_ordered',
            'total' => 'sum',
            'type' => 'number',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('xqty_invoiced', array(
            'header' => Mage::helper('customreports')->__('Qty. Invoiced'),
            'width' => '60px',
            'index' => 'xqty_invoiced',
            'total' => 'sum',
            'type' => 'number',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),

        ));

        $this->addColumn('xqty_shipped', array(
            'header' => Mage::helper('customreports')->__('Qty. Shipped'),
            'width' => '60px',
            'index' => 'xqty_shipped',
            'total' => 'sum',
            'type' => 'number',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('xqty_refunded', array(
            'header' => Mage::helper('customreports')->__('Qty. Refunded'),
            'width' => '60px',
            'index' => 'xqty_refunded',
            'total' => 'sum',
            'type' => 'number',
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_xprice', array(
            'header' => Mage::helper('customreports')->__('Price'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_xprice',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'disable_total' => 1,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),

        ));

        $this->addColumn('base_row_subtotal', array(
            'header' => Mage::helper('customreports')->__('Subtotal'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_row_subtotal',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_tax_amount', array(
            'header' => Mage::helper('customreports')->__('Tax'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_tax_amount',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_discount_amount', array(
            'header' => Mage::helper('customreports')->__('Discounts'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_discount_amount',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('pointreward', array(
            'header' => Mage::helper('customreports')->__('Bilna Credit Used'),
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index' => 'points_to_money',
            'total' => 'sum',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_tax_amount', array(
            'header' => Mage::helper('customreports')->__('Tax'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_tax_amount',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));


        $this->addColumn('base_row_xtotal', array(
            'header' => Mage::helper('customreports')->__('Total'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_row_xtotal',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_row_xtotal_incl_tax', array(
            'header' => Mage::helper('customreports')->__('Total Incl. Tax'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_row_xtotal_incl_tax',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_row_xinvoiced', array(
            'header' => Mage::helper('customreports')->__('Invoiced'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_row_xinvoiced',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_tax_invoiced', array(
            'header' => Mage::helper('customreports')->__('Tax Invoiced'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_tax_invoiced',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_row_xinvoiced_incl_tax', array(
            'header' => Mage::helper('customreports')->__('Invoiced Incl. Tax'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_row_xinvoiced_incl_tax',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_row_xrefunded', array(
            'header' => Mage::helper('customreports')->__('Refunded'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_row_xrefunded',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_tax_xrefunded', array(
            'header' => Mage::helper('customreports')->__('Tax Refunded'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_tax_xrefunded',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('base_row_xrefunded_incl_tax', array(
            'header' => Mage::helper('customreports')->__('Refunded Incl. Tax'),
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'total' => 'sum',
            'index' => 'base_row_xrefunded_incl_tax',
            'column_css_class' => 'nowrap',
            'default' => $def_value,
            'ddl_type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'ddl_size' => '12,2',
            'ddl_options' => array('nullable' => true),
        ));

        $this->addColumn('view_order',
            array(
                'header' => Mage::helper('customreports')->__('View Order'),
                'width' => '70px',
                'type' => 'action',
                'align' => 'left',
                'getter' => 'getOrderId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('customreports')->__('View'),
                        'url' => array(
                            'base' => 'adminhtml/sales_order/view',
                            'params' => array()
                        ),
                        'field' => 'order_id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'order_id',
                'ddl_type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'ddl_size' => null,
                'ddl_options' => array('nullable' => true, 'unsigned' => true),
            ));

        $this->addColumn('view_product',
            array(
                'header' => Mage::helper('customreports')->__('View Product'),
                'width' => '70px',
                'type' => 'action',
                'align' => 'left',
                'getter' => 'getProductId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('customreports')->__('View'),
                        'url' => array(
                            'base' => 'adminhtml/catalog_product/edit',
                            'params' => array()
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'product_id',
                'ddl_type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'ddl_size' => null,
                'ddl_options' => array('nullable' => true, 'unsigned' => true),
            ));

		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

		return parent::_prepareColumns();
	}
}