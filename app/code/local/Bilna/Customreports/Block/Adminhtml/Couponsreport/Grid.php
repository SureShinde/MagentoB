<?php

/**
 * Adminhtml coupons report grid block
 *
 * @category   Bilna
 * @package    Bilna_Couponsreport
 * @author     Bilna Development Team <development@bilna.com>
 */

class Bilna_Customreports_Block_Adminhtml_Couponsreport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId("reportGrid");
		$this->setDefaultSort("main_table.created_at");
		$this->setDefaultDir("ASC");
		$this->setFilterVisibility(false);
		$this->setUseAjax(false);
		//$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel("sales/order")->getCollection();
		$billingAliasName = 'billing_o_a';

		$filter = $this->getRequest()->getParam('filter');
        $params = Mage::helper('adminhtml')->prepareFilterString($filter);

		/*$collection
			->addAttributeToSelect('created_at', 'main_table.created_at');*/
		$collection
			->addAttributeToSelect('coupon_code', 'main_table.coupon_code');
		$collection
			->addAttributeToSelect('base_subtotal', 'main_table.base_subtotal');
		$collection
			->addAttributeToSelect('discount_amount', 'main_table.discount_amount');
		$collection
			->addAttributeToSelect('shipping_amount', 'main_table.shipping_amount');
		$collection
			->addAttributeToSelect('grand_total', 'main_table.grand_total');
		$collection
			->addAttributeToSelect('increment_id', 'main_table.increment_id');
        $collection
        		->addFieldToFilter("main_table.status", array ('neq' => 'pending'));

        /*if( isset($params['order_status_history.created_at']) )
        {
        	$collection
        		->addFieldToFilter("order_status_history.entity_name", 'invoice');
        }elseif()
        {
        	$collection
        		->addFieldToFilter("order_status_history.entity_name", 'invoice');
        }*/

        if( isset($params['from']) && isset($params['to']) )
        {
        	$from = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $params['from'] . '00:00:00')));
        	$to   = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $params['to'] . ' 23:59:59')));

   			$report_type = $params['report_type'];
   			//$collection->addAttributeToSelect('created_at', "$report_type");
			$collection->addAttributeToFilter("$report_type", array('from' => $from, 'to' => $to, 'datetime' => true));
			$collection->addAttributeToSort("$report_type", 'ASC');

        }else{
        	$collection
				->addFieldToFilter("main_table.created_at", array ('eq' => date('Y-m-d H:i:s')));
        }

        if( isset($params['price_rule_type']) &&  $params['price_rule_type'] == 1 )
        {
        	$collection
				->addFieldToFilter("salesrule.rule_id", array (
					array(
						'attribute' => 'rule_id',
						'in'		=> 	explode(",",$params['rules_list'][0])
					)
				));
        }

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

				if (isset($params["coupon_code"])) {
					$collection->addFieldToFilter("main_table.coupon_code", array ("eq" => $params["coupon_code"]));
				} else {
					$collection->addFieldToFilter("main_table.coupon_code", array ('neq' => NULL));
				}

		$collection->getSelect()
			->joinLeft(
	            array('customer' => new Zend_Db_Expr('(SELECT * FROM bilnaview_customer_group)') ),
	            "main_table.customer_id = customer.customer_id",
	            array(
	                "email" 			  => "IF(customer.email<>'', customer.email, "."'GUEST'".")",
	                "customer_id" 		  => "customer.customer_id",
	                "customer_group_code" => "customer.customer_group_code"
	            )
        	)
			->joinLeft(
				array( 'salesrule_coupon' => Mage::getSingleton('core/resource')->getTableName('salesrule/coupon') ),
				"main_table.coupon_code = salesrule_coupon.code",
				array(
					"rule_name" => "salesrule_coupon.code"
				)
			)
			->joinLeft(
				array( 'salesrule' => Mage::getSingleton('core/resource')->getTableName('salesrule') ),
				"salesrule_coupon.rule_id = salesrule.rule_id",
				array(
					"salesrule" => "salesrule.name"
				)
			)
			->joinLeft(
				//array( 'order_status_history' => Mage::getSingleton('core/resource')->getTableName('sales/order_status_history') ),
				array( 'shipping_premiumrate' => new Zend_Db_Expr('(select distinct CONCAT("premiumrate_", REPLACE(delivery_type, " ", "_")) as delivery_type_name, delivery_type from shipping_premiumrate)') ),
				"main_table.shipping_method = shipping_premiumrate.delivery_type_name",
				array(
					'delivery_type' 		=> 'shipping_premiumrate.delivery_type',
					'created_at'			=> "$report_type"
				)
			);

		if( isset($params['report_type']) && $params['report_type'] == 'order_status_history.created_at')
        {
        	$collection
        		->addFieldToFilter("order_status_history.entity_name", 'invoice');

			$collection->getSelect()
				->joinLeft(
					//array( 'order_status_history' => Mage::getSingleton('core/resource')->getTableName('sales/order_status_history') ),
					array( 'order_status_history' => new Zend_Db_Expr('(select * from sales_flat_order_status_history)') ),
					"main_table.entity_id = order_status_history.parent_id",
					array(
						'status' 				=> 'order_status_history.status'
					)
				);

		}

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('created_at', array(
            'header'            => Mage::helper('salesrule')->__('Period'),
            'index'             => 'created_at',
            'width'             => 100,
            'sortable'          => true,
            'period_type'       => $this->getPeriodType(),
            'filter_index' 		=> 'created_at',
            'type'				=> 'date'
        ));

        $this->addColumn('coupon_code', array(
            'header'    => Mage::helper('salesrule')->__('Coupon Code'),
            'sortable'  => false,
            'index'     => 'coupon_code'
        ));

        $this->addColumn('salesrule', array(
            'header'    => Mage::helper('salesrule')->__('Shopping Cart Price Rule'),
            'sortable'  => false,
            'filter_index' 	=> 'salesrule',
            'index'     => 'salesrule'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('salesrule')->__('Email'),
            'sortable'  => false,
            'index'     => 'email',
            'type'      => 'text'
        ));

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('salesrule')->__('Order Number'),
            'sortable'  => false,
            'index'     => 'increment_id',
            'type'      => 'number'
        ));

        $this->addColumn('customer_group_code', array(
            'header'    => Mage::helper('salesrule')->__('User Group'),
            'sortable'  => false,
            'index'     => 'customer_group_code',
            'type'      => 'text'
        ));

        $currencyCode = $this->getCurrentCurrencyCode();
		$rate = $this->getRate($currencyCode);

        $this->addColumn('delivery_type', array(
            'header'    => Mage::helper('salesrule')->__('Handling'),
            'sortable'  => false,
            'index'     => 'delivery_type',
            'type'      => 'text'
        ));

	    $this->addColumn('base_subtotal', array(
	        'header'        => Mage::helper('salesrule')->__('Subtotal Amount'),
	        'sortable'      => false,
	        'type'          => 'currency',
	        'total'         => 'sum',
	        'index'         => 'base_subtotal',
	        'currency_code' => $currencyCode,
	        'rate'          => $rate
	    ));

	    $this->addColumn('discount_amount', array(
	        'header'        => Mage::helper('salesrule')->__('Discount Amount'),
	        'sortable'      => false,
	        'type'          => 'currency',
	        'total'         => 'sum',
	        'index'         => 'discount_amount',
	        'currency_code' => $currencyCode,
	        'rate'          => $rate
	    ));

	    $this->addColumn('shipping_amount', array(
            'header'    => Mage::helper('salesrule')->__('Shipping Amount'),
            'sortable'  => false,
            'index'     => 'shipping_amount',
            'type'      => 'currency',
            'total'         => 'sum',
            'currency_code' => $currencyCode,
	        'rate'          => $rate
        ));

	    $this->addColumn('grand_total', array(
	        'header'        => Mage::helper('salesrule')->__('Total Amount'),
	        'sortable'      => false,
	        'type'          => 'currency',
	        'total'         => 'sum',
	        'index'         => 'grand_total',
	        'currency_code' => $currencyCode,
	        'rate'          => $rate
	    ));

		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

		return parent::_prepareColumns();
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

    /**
     * Get currency rate (base to given currency)
     *
     * @param string|Mage_Directory_Model_Currency $currencyCode
     * @return double
     */
    public function getRate($toCurrency)
    {
        return Mage::app()->getStore()->getBaseCurrency()->getRate($toCurrency);
    }

}
