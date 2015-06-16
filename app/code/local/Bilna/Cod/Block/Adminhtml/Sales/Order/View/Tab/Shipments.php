<?php
/**
 * Description of Bilna_Cod_Block_Adminhtml_Sales_Order_View_Tab_Shipments
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Cod_Block_Adminhtml_Sales_Order_View_Tab_Shipments extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Shipments {
    private $order;
    private $payCode;
    
    public function __construct() {
        parent::__construct();
        
        $this->setId('order_shipments');
        $this->setUseAjax(true);
        
        $this->order = $this->getOrder();
        $this->payCode = $this->order->getPayment()->getMethodInstance()->getCode();
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass() {
        return 'sales/order_shipment_grid_collection';
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('total_qty')
            ->addFieldToSelect('shipping_name')
            ->setOrderFilter($this->getOrder());
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('increment_id', array (
            'header' => Mage::helper('sales')->__('Shipment #'),
            'index' => 'increment_id',
        ));

        $this->addColumn('shipping_name', array (
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('created_at', array (
            'header' => Mage::helper('sales')->__('Date Shipped'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('total_qty', array (
            'header' => Mage::helper('sales')->__('Total Qty'),
            'index' => 'total_qty',
            'type'  => 'number',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() {
        return Mage::registry('current_order');
    }

    public function getRowUrl($row) {
        return $this->getUrl(
            '*/sales_order_shipment/view',
            array (
                'shipment_id'=> $row->getId(),
                'order_id'  => $row->getOrderId()
             )
        );
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/shipments', array ('_current' => true));
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel() {
        return Mage::helper('sales')->__($this->getShipmentsTabLabel());
    }

    public function getTabTitle() {
        return Mage::helper('sales')->__('Order Shipments');
    }

    public function canShowTab() {
        if ($this->getOrder()->getIsVirtual()) {
            return false;
        }
        
        return true;
    }

    public function isHidden() {
        return false;
    }
    
    private function getShipmentsTabLabel() {
        $result = 'Shipments';
        
        if ($this->payCode == 'cod') {
            $result = 'Shipments COD';
        }
        
        return $result;
    }
}
