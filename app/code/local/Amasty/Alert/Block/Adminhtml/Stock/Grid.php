<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */    
class Amasty_Alert_Block_Adminhtml_Stock_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('stockGrid');
        $this->setDefaultSort('cnt');
    }
    
    protected function _prepareCollection()
    {
        $productsTable = Mage::getSingleton('core/resource')->getTableName('catalog/product');
        $c = Mage::getModel('productalert/stock')->getCollection();
        $c->getSelect()
            ->columns(array('cnt' => 'count(*)', 'last_d'=>'MAX(add_date)', 'first_d'=>'MIN(add_date)'))
            ->joinInner(array('e'=> $productsTable), 'e.entity_id = product_id', array('sku'))
            ->where('send_count=0')
            ->group(array('website_id', 'product_id'))
        ;
        
        $this->setCollection($c);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('amalert'); 
    
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id',
                array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'website_id',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));
        } 
        
        $this->addColumn('sku', array(
            'header'    => $hlp->__('SKU'),
            'index'     => 'sku',
        ));
        
        $this->addColumn('cnt', array(
            'header'      => $hlp->__('Count'),
            'index'       => 'cnt',
            'filter'  => false,
        ));
        
        $this->addColumn('first_d', array(
            'header'    => $hlp->__('First Subscription'),
            'index'     => 'first_d',
            'type'      => 'datetime', 
            'width'     => '150px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
            'filter'  => false,
        ));
        $this->addColumn('last_d', array(
            'header'    => $hlp->__('Last Subscription'),
            'index'     => 'last_d',
            'type'      => 'datetime', 
            'width'     => '150px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
            'filter'  => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getProductId())); 
    }
}