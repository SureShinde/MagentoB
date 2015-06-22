<?php
class Phpro_Stockmonitor_Block_Adminhtml_General_Overview_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    protected $tblSalesFlatOrderItem = 'main_table';
    protected $tblSalesFlatOrder = 'sales_flat_order';
    protected $tblCatalogProductEntity = 'catalog_product_entity';

    public function __construct() {
        parent::__construct();
        
        $this->setId('overviewGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        //$this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        //$collection = Mage::getResourceModel('stockmonitor/overview_collection');
        $collection = Mage::getModel('stockmonitor/overview')->getCollection();
        //$collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->getSelect()
            ->joinLeft(array ($this->tblSalesFlatOrder => 'sales_flat_order'), $this->tblSalesFlatOrder . '.entity_id = ' . $this->tblSalesFlatOrderItem . '.order_id', array ('status'))
            ->joinLeft(array ($this->tblCatalogProductEntity => 'catalog_product_entity'), $this->tblCatalogProductEntity . '.entity_id = ' . $this->tblSalesFlatOrderItem . '.product_id', array ('*'))
            ->columns('SUM(' . $this->tblSalesFlatOrderItem . '.qty_ordered) AS sum_qty_ordered')
            ->group(array ($this->tblSalesFlatOrder . '.status', $this->tblSalesFlatOrderItem . '.product_id', $this->tblSalesFlatOrderItem . '.sku'));
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('entity_id', array (
            'header' => Mage::helper('catalog')->__('Product ID'),
            'sortable' => true,
            'width' => '30px',
            'type' => 'number',
            'index' => 'entity_id',
            'filter_index' => $this->tblCatalogProductEntity . '.entity_id'
        ));
		
        $this->addColumn('name', array (
            'header' => Mage::helper('catalog')->__('Name'),
            'sortable' => true,
            //'width' => '60px',
            'type' => 'text',
            'index' => 'name',
            'filter_index' => $this->tblSalesFlatOrderItem . '.name'
        ));
		
        $this->addColumn('sku', array (
            'header' => Mage::helper('catalog')->__('SKU'),
            'sortable' => true,
            'width' => '160px',
            'index' => 'sku',
            'filter_index' => $this->tblSalesFlatOrderItem . '.sku'
        ));
		
        $this->addColumn('type_id', array (
            'header' => Mage::helper('catalog')->__('Type'),
            'sortable' => true,
            'width' => '100px',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            'index' => 'type_id',
            'filter_index' => $this->tblCatalogProductEntity . '.type_id'
        ));
		
        $this->addColumn('sum_qty_ordered', array (
            'header' => Mage::helper('catalog')->__('QTY'),
            'sortable' => true,
            'width' => '60px',
            'type' => 'number',
            'index' => 'sum_qty_ordered',
            'filter' => false
            //'filter_index' => 'SUM(' . $this->tblSalesFlatOrderItem . '.qty_ordered)'
            //'filter_condition_callback' => array ($this, 'filterSumQtyOrder')
        ));

        $this->addColumn('status', array (
            'header' => Mage::helper('sales')->__('Status'),
            'type' => 'options',
            'width' => '140px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'index' => 'status',
            'filter_index' => $this->tblSalesFlatOrder . '.status'
        ));
		
        $this->addColumn('updated_at', array (
            'header' => Mage::helper('sales')->__('Updated at'),
            'type' => 'datetime',
            'width' => '170px',
            'index' => 'updated_at',
            'filter_index' => $this->tblCatalogProductEntity . '.update_at'
        ));
		
        if (Mage::getSingleton('admin/session')->isAllowed('catalog/product/actions/edit')) {
            $this->addColumn('action', array (
                'header' => Mage::helper('catalog')->__('Action'),
                'width' => '40px',
                'align' => 'center',
                'type' => 'action',
                'getter' => 'getProductId',
                'actions' => array (
                    array (
                        'caption' => Mage::helper('catalog')->__('View'),
                        'url' => array ('base'=>'*/catalog_product/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        }

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }
    
    /**
     * Grid with Ajax Request
     */
//    public function getGridUrl() {
//        return $this->getUrl('*/*/grid', array ('_current' => true));
//    }
}
