<?php

class Phpro_Stockmonitor_Block_Adminhtml_General_Stockmovement_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('overviewGrid');
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('stockmonitor/stockmovement')->getCollection();

        $collection->getSelect()
                ->join(array('product_entity' => 'catalog_product_entity'), 'main_table.product_id = product_entity.entity_id', array('product_entity.entity_id', 'product_entity.type_id', 'product_entity.sku'))
                ->join(array('product_varchar' => 'catalog_product_entity_varchar'), 'main_table.product_id=product_varchar.entity_id', array('product_varchar.value AS product_name'))
                ->where("product_varchar.attribute_id=(SELECT attribute_id FROM eav_attribute WHERE attribute_code='name' AND entity_type_id=(SELECT entity_type_id FROM eav_entity_type WHERE entity_model='catalog/product'))");

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        Mage::log(Mage::getSingleton('sales/order_config')->getStatuses());
        $this->addColumn('product_id', array(
            'header' => Mage::helper('catalog')->__('ProductID'),
            'sortable' => true,
            'width' => '60px',
            'index' => 'product_id'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'sortable' => true,
            'width' => '60px',
            'index' => 'product_name'
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'sortable' => true,
            'width' => '60px',
            'index' => 'sku'
        ));

        $this->addColumn('increment_id', array(
            'header' => Mage::helper('catalog')->__('Order #'),
            'sortable' => true,
            'width' => '60px',
            'index' => 'type_id'
        ));

        $this->addColumn('qty_change', array(
            'header' => Mage::helper('catalog')->__('QTY change'),
            'sortable' => true,
            'width' => '60px',
            'type' => 'text',
            'index' => 'qty_change'
        ));

        $this->addColumn('action_performed', array(
            'header' => Mage::helper('catalog')->__('Action Performed'),
            'sortable' => true,
            'width' => '60px',
            'type' => 'text',
            'index' => 'action_performed',
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
            $this->addColumn('action', array(
                'header' => Mage::helper('sales')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getOrderId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View Orders'),
                        'url' => array('base' => '*/sales_order/view'),
                        'field' => 'order_id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'renderer' => 'Phpro_Stockmonitor_Block_Adminhtml_Grid_Renderer_Action',
                'is_system' => true,
            ));
        }

        $this->addExportType('*/*/exportCsv', Mage::helper('catalog')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('catalog')->__('XML'));

        return parent::_prepareColumns();
    }

}