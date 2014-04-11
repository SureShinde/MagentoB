<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Block_Adminhtml_Changelog_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct()
    {
        parent::__construct();
        $this->setId('changelog_grid');
        $this->setDefaultSort('created_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rocketweb_netsuite/changelog')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Id'),
            'align'     =>  'left',
            'width'     =>  '50px',
            'sortable'  =>   false,
            'index'     =>  'id',
        ));

        $this->addColumn('action', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Action'),
            'align'     =>  'left',
            'sortable'  =>   false,
            'index'     =>  'action',
        ));

        $this->addColumn('internal_id', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Identifier'),
            'align'     =>  'left',
            'sortable'  =>   false,
            'index'     =>  'internal_id'
        ));
        $this->addColumn('comment', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Comment'),
            'align'     =>  'left',
            'sortable'  =>   false,
            'index'     =>  'comment',
        ));
        $this->addColumn('created_date', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Created At'),
            'align'     =>  'left',
            'index'     =>  'created_date',
            'sortable'  =>   false,
            'renderer'  =>  'RocketWeb_Netsuite_Block_Adminhtml_Status_Grid_Renderer_Timestamp'
        ));

        $this->addColumn('actions_delete',
            array(
                'header'    => Mage::helper('sales')->__('Delete'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(

                    array(
                        'caption' => Mage::helper('sales')->__('Delete'),
                        'url'     => array('base'=>'*/*/delete'),
                        'field'   => 'id',
                        'onclick'  => 'return confirm("Are you sure?")'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        parent::_prepareMassaction();

        $this->setMassactionIdField('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array('label'=>'Delete',
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('catalog')->__('Are you sure?'))
        );

        return $this;
    }
}