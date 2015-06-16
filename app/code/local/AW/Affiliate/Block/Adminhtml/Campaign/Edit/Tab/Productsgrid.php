<?php

class AW_Affiliate_Block_Adminhtml_Campaign_Edit_Tab_Productsgrid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awaffiliate_productsselector');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        //$this->setRowInitCallback('awfBForm.productGridRowInit.bind(awfBForm)');
        $this->setDefaultFilter(array('in_products'=>1));
        $this->setSaveParametersInSession(false);
    }

    protected function getWebsite()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->getWebsite();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
        ;
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }
        $_visibility = array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
        );
        $collection->addAttributeToFilter('visibility', $_visibility);

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;

    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $ids = $this->_getSelectedProducts();
            if (empty($ids)) {
                $ids = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$ids));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$ids));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            array(
                'header_css_class'  => 'a-center',
                'type'              => 'checkbox',
                'name'              => 'product',
                'align'             => 'center',
                'index'             => 'entity_id',
                'values'            => $this->_getSelectedProducts()
                //'renderer' => 'AW_Affiliate_Block_Adminhtml_Widget_Grid_Column_Renderer_Checkbox'
            )
        );

        $this->addColumn(
            'entity_id',
            array(
                'header'    => Mage::helper('awaffiliate')->__('ID'),
                'sortable'  => true,
                'width'     => '60',
                'index'     => 'entity_id'
            )
        );

        $this->addColumn(
            'product_name',
            array(
                'header'    => Mage::helper('awaffiliate')->__('Product Name'),
                'index'     => 'name'
            )
        );

        $this->addColumn(
            'sku',
            array(
                'header'    => Mage::helper('awaffiliate')->__('SKU'),
                'width'     => '80',
                'index'     => 'sku'
            )
        );

        $store = $this->_getStore();
        $this->addColumn(
            'price',
            array(
                'header'    => Mage::helper('awaffiliate')->__('Price'),
                'type'      => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index'     => 'price',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'websites',
                array(
                    'header'    => Mage::helper('awaffiliate')->__('Websites'),
                    'width'     => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                )
            );
        }

        $this->addColumn(
            'image',
            array(
                'header'    => $this->__('Product Image'),
                'width'     => '150',
                'index'     => 'entity_id',
                'sortable'  => false,
                'filter'    => false,
                'renderer'  => 'AW_Affiliate_Block_Adminhtml_Widget_Grid_Column_Renderer_Imagepreview',
            )
        );

        $this->addColumn(
            'position', 
            array(
                'header'            => Mage::helper('catalog')->__('Position'),
                'name'              => 'position',
                'width'             => 60,
                'type'              => 'number',
                'validate_class'    => 'validate-number',
                'index'             => 'position',
                'editable'          => true,
                'edit_only'         => true
            ));

        return parent::_prepareColumns();

    }

    protected function _getSelectedProducts()
    {
        $products = array_keys($this->getSelectedProducts());
        return $products;
    }

    public function getSelectedProducts()
    {
        $campaign_id = $this->getRequest()->getParam('id');
        if(!isset($campaign_id)) {
            $campaign_id = 0;
        }
        $collection = Mage::getModel('awaffiliate/products')->getCollection();
        $collection->addFieldToFilter('campaign_id',$campaign_id);
        $prodIds = array();
        foreach($collection as $obj){
            $prodIds[$obj->getProductId()] = array('position'=>$obj->getPosition());
        }
        return $prodIds;
    }

    protected function _getStore()
    {
        $storeId = $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', array('_current' => true));
    }

    public function getRowUrl($item)
    {
        return null;
    }

}
