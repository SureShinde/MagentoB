<?php
class Phpro_Stockmonitor_Block_Adminhtml_Catalog_Product_Edit_Tab_Overview extends Mage_Adminhtml_Block_Widget_Grid
 implements Mage_Adminhtml_Block_Widget_Tab_Interface{
	 
	public function __construct()
    {
        parent::__construct();
        $this->setId('overview_grid');
		$this->setDefaultSort('updated_at');
		$this->setDefaultDir('desc');
        $this->setSkipGenerateContent(true);
        $this->setUseAjax(true);
		
		//$this->setCountTotals(true);
		
        if ($this->_getProduct()->getId()) {
            $this->setDefaultFilter(array('in_products'=>1));
        }
    }
    
    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/stockmonitor_overview/overviewGrid', array('_current'=>true));
    }
    public function getTabClass()
    {
        return 'ajax';
    }
    
    /**
     * Retrieve currently edited product model
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }
    
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            else {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    /**
     * Prepare collection
     *
     * @return Phpro_Promoset_Block_Adminhtml_Catalog_Product_Edit_Tab_Relation
     */
    protected function _prepareCollection()
    {
		
		$collection = Mage::getModel('sales/order_item')->getCollection()
		->addAttributeToFilter('product_id', array(
			'eq' => $this->_getProduct()->getEntityId(),
		));
		
		$collection->getSelect()->join(array('sales_table'=>'sales_flat_order'), 'main_table.order_id = sales_table.entity_id', array('sales_table.entity_id', 'sales_table.state', 'sales_table.status', 'sales_table.increment_id'));

		$this->setCollection($collection);
		
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
		/*
		$this->addColumn('item_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'item_id'
        ));
		*/
		$this->addColumn('increment_id', array(
            'header'    => Mage::helper('catalog')->__('Order #'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'increment_id'
        ));
		
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }
		
		$this->addColumn('qty_backordered', array(
            'header'    => Mage::helper('catalog')->__('Qty Backordered'),
            'sortable'  => true,
            'width'     => '60px',
			'total'     =>'sum',
			'type'      => 'number',
            'index'     => 'qty_backordered'
        ));
		
		$this->addColumn('qty_canceled', array(
            'header'    => Mage::helper('catalog')->__('Qty Canceled'),
            'sortable'  => true,
            'width'     => '60px',
			'type'      => 'number',
            'index'     => 'qty_canceled'
        ));
		
		$this->addColumn('qty_invoiced', array(
            'header'    => Mage::helper('catalog')->__('Qty Invoiced'),
            'sortable'  => true,
            'width'     => '60px',
			'type'      => 'number',
            'index'     => 'qty_invoiced'
        ));
		
		$this->addColumn('qty_ordered', array(
            'header'    => Mage::helper('catalog')->__('Qty Ordered'),
            'sortable'  => true,
            'width'     => '60px',
			//'total'     => 'sum',
			'index'     => 'qty_ordered',
			'type'      => 'number',
            'index'     => 'qty_ordered'
        ));
		
		$this->addColumn('qty_refunded', array(
            'header'    => Mage::helper('catalog')->__('Qty Refunded'),
            'sortable'  => true,
            'width'     => '60px',
			'type'      => 'number',
            'index'     => 'qty_refunded'
        ));
		
		$this->addColumn('qty_shipped', array(
            'header'    => Mage::helper('catalog')->__('Qty Shipped'),
            'sortable'  => true,
            'width'     => '60px',
			'type'      => 'number',
            'index'     => 'qty_shipped'
        ));
		
		$this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'sortable'  => true,
            'width'     => '60px',
			'type'      => 'options',
			'options'	=> Mage::getSingleton('sales/order_config')->getStatuses(),
            'index'     => 'status'
        ));
		
        $this->addColumn('updated_at', array(
            'header' => Mage::helper('sales')->__('Updated at'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
/*
        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
*/
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
		//$this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }
    
    public function getGridUrl()
    {
        return $this->_getData('grid_url')
            ? $this->_getData('grid_url') : $this->getUrl('adminhtml/stockmonitor_overview/overviewGridOnly', array('_current'=>true));
    }
	
    public function getSelectedOrders()
    {
		$products = array();
		/*
		foreach (Mage::registry('current_product')->getUpSellProducts() as $product) {
			$products[$product->getId()] = array('position' => $product->getPosition());
		}
		*/
		return $products;
	}
    
    public function getTabLabel()
    {
        return Mage::helper('catalog')->__('Order Overview');
    }
	
    public function getTabTitle()
    {
        return Mage::helper('catalog')->__('Order Overview');
    }
	
    public function canShowTab()
    {
        return true;
    }
	
    public function isHidden()
    {
        return false;
    }
}