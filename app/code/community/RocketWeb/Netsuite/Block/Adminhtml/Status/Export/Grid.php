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
class RocketWeb_Netsuite_Block_Adminhtml_Status_Export_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        
        $this->setId('export_status_grid');
        $this->setDefaultSort('created');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
	
    protected function _prepareCollection() {
        $collection = Mage::getModel('rocketweb_netsuite/queue_message')->getCollection();
        $collection->addFieldToFilter('queue_id', Mage::helper('rocketweb_netsuite/queue')->getQueueId(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
	
    protected function _prepareColumns() {
        $this->addColumn('message_id', array ('header' =>  Mage::helper('rocketweb_netsuite')->__('Message Id'),
            'align' => 'left',
            'width' => '50px',
            'sortable' => false,
            'index' => 'message_id',
        ));
        $this->addColumn('action', array ('header' =>  Mage::helper('rocketweb_netsuite')->__('Operation'),
            'align' => 'left',
            'sortable' => false,
            'index' => 'action',
        ));
        $this->addColumn('item_id', array ('header' =>  Mage::helper('rocketweb_netsuite')->__('Item ID'),
            'align' => 'left',
            'sortable' => false,
            'index' => 'item_id',
            'filter_condition_callback' => array ($this, 'filterCallbackItemId'),
        ));
        $this->addColumn('created', array ('header' =>  Mage::helper('rocketweb_netsuite')->__('Created At'),
            'align' => 'left',
            'index' => 'created',
            'sortable' => false,
            'renderer' => 'RocketWeb_Netsuite_Block_Adminhtml_Status_Grid_Renderer_Timestamp'
        ));
        $this->addColumn('actions', array (
            'header' => Mage::helper('sales')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getMessageId',
            'actions' => array (
                array (
                    'caption' => Mage::helper('sales')->__('Delete'),
                    'url' => array ('base' => '*/*/delete'),
                    'field' => 'message_id',
                    'onclick' => 'return confirm("Are you sure?")'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
		
        return parent::_prepareColumns();
    }
    
    protected function filterCallbackItemId($collection, $column) {
        $condition = $column->getFilter()->getCondition();
        $filter = $condition['like'];
        $collection->getSelect()->where("RIGHT(body,CHAR_LENGTH(body) - LOCATE('|',body)) LIKE $filter");
        
        return $this;
    }

    protected function _prepareMassaction() {
        parent::_prepareMassaction();

        $this->setMassactionIdField('message_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array ('label'=>'Delete',
                'url'  => $this->getUrl('*/*/massDeleteExport'),
                'confirm' => Mage::helper('catalog')->__('Are you sure?'))
        );

        return $this;
    }
}