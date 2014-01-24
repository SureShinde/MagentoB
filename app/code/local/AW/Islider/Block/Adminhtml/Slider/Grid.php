<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Block_Adminhtml_Slider_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('awislider')
            ->setSaveParametersInSession(TRUE)
            ->setUseAjax(FALSE);
    }
    
    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'index' => 'id',
            'width' => '100px'
        ));
        
        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'index' => 'name'
        ));
        
        $this->addColumn('block_id', array(
            'header' => $this->__('Block ID'),
            'index' => 'block_id'
        ));
        
        if(!Mage::app()->isSingleStoreMode())
            $this->addColumn('store', array(
                'header' => $this->__('Store View'),
                'index' => 'store',
                'sortable' => FALSE,
                'type' => 'store',
                'store_all' => TRUE,
                'store_view' => TRUE,
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                'filter_condition_callback' => array($this, '_filterStoreCondition')
            ));
        
        $this->addColumn('is_active', array(
            'header' => $this->__('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => Mage::getModel('awislider/source_status')->toShortOptionArray(),
            'width' => '200px'
        ));
        
        if(Mage::getSingleton('admin/session')->isAllowed('cms/awislider/new')) {
            $this->addColumn('actions', array(
                'header' => $this->__('Actions'),
                'width' => '150px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    ),
                    array(
                        'caption' => $this->__('Delete'),
                        'url' => array('base' => '*/*/delete'),
                        'field' => 'id',
                        'confirm' => $this->__('Are you sure you want do this?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ));
        }
        
        return parent::_prepareColumns();
    }
    
    protected function _prepareCollection() {
        $this->setCollection(Mage::getModel('awislider/slider')->getCollection());
        return parent::_prepareCollection();
    }
    
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getData('id')));
    }

    protected function _filterStoreCondition($collection, $column) {
        if(!($value = $column->getFilter()->getValue())) return;
        $collection->addStoreFilter($value);
    }

    protected function _prepareMassaction() {
        if(!Mage::getSingleton('admin/session')->isAllowed('cms/awislider/new'))
            return $this;
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('awislider');
        $this->getMassactionBlock()->addItem('status',
            array(
                'label'=> $this->__('Change status'),
                'url'  => $this->getUrl('*/*/massactionstatus', array('_current'=>true)),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $this->__('Status'),
                        'values' => Mage::getModel('awislider/source_status')->toOptionArray()
                    )
                )
            )
        );
        return $this;
    }
}
