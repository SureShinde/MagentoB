<?php
/**
 * Description of Bilna_Paymethod_Block_Adminhtml_Sales_Order_Grid
 *
 * @path    app/code/local/Bilna/Paymethod/Block/Adminhtml/Sales/Order/Grid.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {
    protected $_paymentMethodTitles = array ();
    
    public function __construct() {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass() {
        return 'sales/order_grid_collection';
    }
    
    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->join(array ('order' => 'sales/order'), 'main_table.entity_id = order.entity_id', 'trx_from');
        $collection->join(array ('payment' => 'sales/order_payment'), 'main_table.entity_id = parent_id', 'method');
        $collection->addFilterToMap('increment_id', 'main_table.increment_id');
        $collection->addFilterToMap('store_id', 'main_table.store_id');
        $collection->addFilterToMap('created_at', 'main_table.created_at');
        $collection->addFilterToMap('group_id', 'customer_entity.group_id');
        $collection->getSelect()->joinLeft(
            array ('customer_entity'),
            'main_table.customer_id = customer_entity.entity_id',
            array ('group_id' => 'customer_entity.group_id')
        );
        
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns() {
        $salesHelper = Mage::helper('sales');
        
        //Payment Method Information
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $methods = array ();
        
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->_getPaymentMethodTitle($paymentCode);
            $methods[$paymentCode] = $paymentTitle;
        }

        $this->addColumn('real_order_id', array (
            'header'=> $salesHelper->__('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id',
        ));

//        if (!Mage::app()->isSingleStoreMode()) {
//            $this->addColumn('store_id', array (
//                'header' => Mage::helper('sales')->__('Purchased From (Store)'),
//                'index' => 'store_id',
//                'filter_index' => 'main_table.store_id',
//                'type' => 'store',
//                'store_view' => true,
//                'display_deleted' => true,
//            ));
//        }

        $this->addColumn('created_at', array (
            'header' => $salesHelper->__('Purchased On'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array (
            'header' => $salesHelper->__('Bill to Name'),
            'index' => 'billing_name',
            'filter_index' => 'main_table.billing_name',
        ));

        $this->addColumn('shipping_name', array (
            'header' => $salesHelper->__('Ship to Name'),
            'index' => 'shipping_name',
            'filter_index' => 'main_table.shipping_name',
        ));
        
        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array ('gt' => 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group_id', array (
            'header' => $salesHelper->__('Group'),
            'index' => 'group_id',
            'type' => 'options',
            'options' => $groups,
        ));
		
        $this->addColumn('method', array (
            'header' => $salesHelper->__('Payment Method'),
            'index' => 'method',
            'filter_index' => 'payment.method',
            'type' => 'options',
            'width' => '70px',
            'options' => $methods,
        ));

        $this->addColumn('base_grand_total', array (
            'header' => $salesHelper->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'filter_index' => 'main_table.base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array (
            'header' => $salesHelper->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'filter_index' => 'main_table.grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array (
            'header' => $salesHelper->__('Status'),
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
        
        $this->addColumn('trx_from', array (
            'header' => $salesHelper->__('Order From'),
            'index' => 'trx_from',
            'filter_index' => 'order.trx_from',
            'type' => 'options',
            'width' => '50px',
            'options' => $this->_getTrxFroms(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array (
                    'header' => $salesHelper->__('Action'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array (
                        array (
                            'caption' => $salesHelper->__('View'),
                            'url' => array ('base'=>'*/sales_order/view'),
                            'field' => 'order_id'
                        )
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true,
                )
            );
        }
        
        $this->addRssList('rss/order/new', $salesHelper->__('New Order RSS'));
        $this->addExportType('*/*/exportCsv', $salesHelper->__('CSV'));
        $this->addExportType('*/*/exportExcel', $salesHelper->__('Excel XML'));

        return $this;
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array (
                 'label'=> Mage::helper('sales')->__('Cancel'),
                 'url' => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array (
                 'label'=> Mage::helper('sales')->__('Hold'),
                 'url' => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array (
                 'label'=> Mage::helper('sales')->__('Unhold'),
                 'url' => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfdetail_order', array (
            'label'=> Mage::helper('sales')->__('Print PDF Order'),
            'url' => $this->getUrl('*/sales_order/pdfdetail'),
        ));

        $this->getMassactionBlock()->addItem('pdfcod_rpx', array (
            'label'=> Mage::helper('sales')->__('Export RPX CSV'),
            'url' => $this->getUrl('*/sales_order/pdfcodrpx'),
        ));

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array (
             'label'=> Mage::helper('sales')->__('Print Invoices'),
             'url' => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array (
             'label'=> Mage::helper('sales')->__('Print Packingslips'),
             'url' => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array (
             'label'=> Mage::helper('sales')->__('Print Credit Memos'),
             'url' => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array (
             'label'=> Mage::helper('sales')->__('Print All'),
             'url' => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array (
             'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
             'url' => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        return $this;
    }

    public function getRowUrl($row) {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array ('order_id' => $row->getId()));
        }
        
        return false;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array ('_current' => true));
    }
    
    protected function _getPaymentMethodTitle($paymentCode) {
        if (isset ($this->_paymentMethodTitles[$paymentCode]['title'])) {
            return $this->_paymentMethodTitles[$paymentCode]['title'];
        }
        
        return ($this->_paymentMethodTitles[$paymentCode]['title'] = Mage::getStoreConfig('payment/' . $paymentCode . '/title'));
    }
    
    protected function _getTrxFroms() {
        $salesHelper = Mage::helper('sales');
        $trxFrom = [
            1 => $salesHelper->__('Magento'),
            2 => $salesHelper->__('Logan'),
            3 => $salesHelper->__('Mobile Apps'),
            4 => $salesHelper->__('Affiliate')
        ];
        
        return $trxFrom;
    }
}
