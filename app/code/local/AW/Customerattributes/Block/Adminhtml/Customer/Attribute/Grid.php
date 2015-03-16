<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Customerattributes_Block_Adminhtml_Customer_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('aw_customerattributesGrid');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('aw_customerattributes/attribute_collection')
            ->joinAttributeLabelsForStore()
        ;
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'attribute_id',
            array(
                 'header'       => $this->__('ID'),
                 'index'        => 'attribute_id',
                 'type'         => 'number',
                 'width'        => '20px',
                 'filter_index' => 'main_table.attribute_id',
            )
        );

        $this->addColumn(
            'code',
            array(
                 'header' => $this->__('Attribute Code'),
                 'index'  => 'code',
            )
        );

        $this->addColumn(
            'label',
            array(
                'header'       => $this->__('Attribute Label'),
                'index'        => 'label',
                'filter_index' => 'attribute_label.value',
            )
        );

        $this->addColumn(
            'type',
            array(
                'header'  => $this->__('Type'),
                'index'   => 'type',
                'type'    => 'options',
                'options' => Mage::getModel('aw_customerattributes/source_type')->toArray(),
                'width'   => '150px',
            )
        );

        $this->addColumn(
            'is_enabled',
            array(
                'header'  => $this->__('Is Enabled'),
                'index'   => 'is_enabled',
                'type'    => 'options',
                'options' => Mage::getModel('aw_customerattributes/source_yesno')->toArray(),
                'width'   => '100px',
            )
        );

        $this->addColumn(
            'sort_order',
            array(
                'header' => $this->__('Sort Order'),
                'type'   => 'number',
                'index'  => 'sort_order',
                'width'  => '100px',
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'   => $this->__('Action'),
                'width'    => '50px',
                'type'     => 'action',
                'getter'   => 'getId',
                'actions'  => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id',
                    )
                ),
                'filter'   => false,
                'sortable' => false,
                'index'    => 'stores',
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('attribute_id');
        $this->getMassactionBlock()->setFormFieldName('attribute');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'   => Mage::helper('aw_customerattributes')->__('Delete'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('aw_customerattributes')->__('Are you sure?'),
            )
        );

        $statuses = array(
            array(
                'value' => 1,
                'label' => Mage::helper('aw_customerattributes')->__('Enable'),
            ),
            array(
                'value' => 0,
                'label' => Mage::helper('aw_customerattributes')->__('Disable'),
            ),
        );

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('aw_customerattributes')->__('Change Status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current' => true)),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('aw_customerattributes')->__('Status'),
                        'values' => $statuses,
                    )
                ),
            )
        );

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit/', array('id' => $row->getId()));
    }
}