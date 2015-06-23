<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 * Brands grid block
 */
class Belvg_Brands_Block_Adminhtml_Brands_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Current store
     *
     * @var Mage_Core_Model_Store
     */
    protected $store = NULL;

    /**
     * Init sorting
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('brandsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(TRUE);
    }

    /**
     * Get current store
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        if (is_null($this->store)) {
            $storeId = (int) $this->getRequest()->getParam('store', Mage_Core_Model_App::ADMIN_STORE_ID);
            $this->store = Mage::app()->getStore($storeId);
        }

        return $this->store;
    }

    /**
     * Prepare grid collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {

        $store = $this->_getStore();
        $collection = Mage::getModel('brands/brands')
                ->getCollection();
        $collection
                ->addAttributeToSelect(array('id', 'title', 'active'))
                ->setStore($store);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
                'header' => Mage::helper('catalog')->__('ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'entity_id',
        ));

        $this->addColumn('image', array(
                'header' => Mage::helper('catalog')->__('Image'),
                'width' => '75px',
                'index' => 'image',
                'filter' => FALSE,
                'sortable' => FALSE,
                'renderer' => 'brands/adminhtml_brands_grid_renderer_image',
        ));

        $this->addColumn('title', array(
                'header' => Mage::helper('catalog')->__('Title'),
                'align' => 'left',
                'index' => 'title',
        ));

        $this->addColumn('active', array(
                'header' => Mage::helper('catalog')->__('Status'),
                'index' => 'active',
                'type' => 'options',
                'align' => 'center',
                'options' => array(
                        0 => Mage::helper('adminhtml')->__('Disabled'),
                        1 => Mage::helper('adminhtml')->__('Enabled')
                ),
        ));

        $this->addColumn('created_at', array(
                'header' => Mage::helper('brands')->__('Created at'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'created_at',
                'gmtoffset' => TRUE
        ));

        $this->addColumn('updated_at', array(
                'header' => Mage::helper('brands')->__('Updated at'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'updated_at',
                'gmtoffset' => TRUE
        ));

        $this->addColumn('action', array(
                'header' => Mage::helper('adminhtml')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                        array(
                                'caption' => Mage::helper('adminhtml')->__('Edit'),
                                'url' => array('base' => '*/*/edit'),
                                'field' => 'id'
                        )
                ),
                'filter' => FALSE,
                'sortable' => FALSE,
                'index' => 'stores',
                'is_system' => TRUE,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get row url
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        $params = array();
        $params['id'] = $row->getId();

        if ($store_id = $this->_getStore()->getId()) {
            $params['store'] = $store_id;
        }

        return $this->getUrl('*/*/edit', $params);
    }

    /**
     * Build mass delete and mass update status elements
     *
     * @return Belvg_Brands_Block_Adminhtml_Brands_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('brand');

        $this->getMassactionBlock()->addItem('delete', array(
                'label' => Mage::helper('adminhtml')->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('adminhtml/system_config_source_enabledisable')->toOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
                'label' => Mage::helper('catalog')->__('Change status'),
                'url' => $this->getUrl('*/*/massStatus', array('_current' => TRUE)),
                'additional' => array(
                        'visibility' => array(
                                'name' => 'status',
                                'type' => 'select',
                                'class' => 'required-entry',
                                'label' => Mage::helper('brands')->__('Status'),
                                'values' => $statuses
                        )
                )
        ));

        Mage::dispatchEvent('adminhtml_brands_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

}