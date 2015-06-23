<?php
class Phpro_Stockmonitor_Block_Adminhtml_Catalog_Product_Edit_Tab_Stockmovement extends Mage_Adminhtml_Block_Widget_Grid
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
        return $this->getUrl('adminhtml/stockmonitor_stockmovement/grid', array('_current'=>true));
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
		
		$collection = Mage::getModel('stockmonitor/stockmovement')->getCollection()
		->addAttributeToFilter('product_id', array(
			'eq' => $this->_getProduct()->getEntityId(),
		));
		
		$this->setCollection($collection);
		
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
        /*	
		$this->addColumn('movement_id', array(
            'header'    => Mage::helper('catalog')->__('Movement id'),
            'sortable'  => true,
            'width'     => '60px',
			'type'      => 'number',
            'index'     => 'movement_id'
        ));
        */
        /*		
		$this->addColumn('product_id', array(
            'header'    => Mage::helper('catalog')->__('Product id'),
            'sortable'  => true,
            'width'     => '60px',
			'type'      => 'number',
            'index'     => 'product_id'
        ));
		*/
		/*
		$this->addColumn('order_id', array(
            'header'    => Mage::helper('catalog')->__('Order id'),
            'sortable'  => true,
            'width'     => '60px',
			'type'      => 'number',
            'index'     => 'order_id'
        ));
		*/
		$this->addColumn('increment_id', array(
            'header'    => Mage::helper('catalog')->__('Order #'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'increment_id'
        ));
		
		$this->addColumn('qty_change', array(
            'header'    => Mage::helper('catalog')->__('QTY change'),
            'sortable'  => true,
            'width'     => '60px',
			'type'		=> 'text',
            'index'     => 'qty_change'
        ));
		
		$this->addColumn('action_performed', array(
            'header'    => Mage::helper('catalog')->__('Action Performed'),
            'sortable'  => true,
            'width'     => '60px',
			'type'		=> 'text',
            'index'     => 'action_performed',
            'renderer' => 'Phpro_Stockmonitor_Block_Adminhtml_Grid_Renderer_Action',
            'type' => 'options',
            'options' => array('Product_Update' => 'Product Update', 'Credit_Memo' => 'Credit Memo', 'Create_Order_Front' => 'Create Order Front', 'Cancel_Order' => 'Cancel Order', 'Create_Order_Back' => 'Create Order Back'),       
        ));
		
        $this->addColumn('updated_at', array(
            'header' => Mage::helper('sales')->__('Data update'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
		
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getOrderId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View Order'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                    'renderer' => 'Phpro_Stockmonitor_Block_Adminhtml_Grid_Renderer_Action',
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
            ? $this->_getData('grid_url') : $this->getUrl('adminhtml/stockmonitor_stockmovement/gridOnly', array('_current'=>true));
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
        return Mage::helper('catalog')->__('Stock Movements');
    }
	
    public function getTabTitle()
    {
        return Mage::helper('catalog')->__('Stock Movements');
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