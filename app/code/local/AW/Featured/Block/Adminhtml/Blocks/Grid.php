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
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Block_Adminhtml_Blocks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('featured')
            ->setSaveParametersInSession(TRUE)
            ->setUseAjax(FALSE)
        ;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header' => $this->__('ID'),
                'index' => 'id',
                'width' => '100px'
            )
        );

        $this->addColumn(
            'block_name',
            array(
                'header' => $this->__('Name'),
                'index' => 'block_name'
            )
        );

        $this->addColumn(
            'block_id',
            array(
                'header' => $this->__('Block ID'),
                'index' => 'block_id'
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store',
                array(
                    'header' => $this->__('Store View'),
                    'index' => 'store',
                    'sortable' => FALSE,
                    'type' => 'store',
                    'store_all' => TRUE,
                    'store_view' => TRUE,
                    'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                    'filter_condition_callback' => array($this, '_filterStoreCondition')
                )
            );
        }

        $this->addColumn(
            'representation',
            array(
                'header' => $this->__('Representation'),
                'index' => 'type',
                'type' => 'options',
                'options' => Mage::getModel('awfeatured/source_representation')->toShortOptionArray()
            )
        );

        $this->addColumn(
            'autoposition',
            array(
                'header' => $this->__('Automatic Position'),
                'index' => 'autoposition',
                'type' => 'options',
                'options' => Mage::getModel('awfeatured/source_autoposition')->toShortOptionArray()
            )
        );

        $this->addColumn(
            'automation_type',
            array(
                'header' => $this->__('Automation Type'),
                'index' => 'automation_type',
                'type' => 'options',
                'options' => Mage::getModel('awfeatured/source_automation')->toShortOptionArray()
            )
        );

        $this->addColumn(
            'is_active',
            array(
                'header' => $this->__('Enabled'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => Mage::getModel('awfeatured/source_status')->toShortOptionArray()
            )
        );

        $this->addColumn(
            'actions',
            array(
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
                        'caption' => $this->__('Duplicate'),
                        'url' => array('base' => '*/*/duplicate'),
                        'field' => 'id',
                        'confirm' => $this->__('Are you sure you want to do this?')
                    ),
                    array(
                        'caption' => $this->__('Delete'),
                        'url' => array('base' => '*/*/delete'),
                        'field' => 'id',
                        'confirm' => $this->__('Are you sure you want to do this?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            )
        );
        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('awfeatured/blocks')->getCollection());
        return parent::_prepareCollection();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getData('id')));
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        $collection->setStoreFilter($value);
    }
}
