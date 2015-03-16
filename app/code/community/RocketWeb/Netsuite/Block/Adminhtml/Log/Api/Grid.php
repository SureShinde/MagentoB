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
class RocketWeb_Netsuite_Block_Adminhtml_Log_Api_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct()
	{
		parent::__construct();
		$this->setId('log_api_grid');
		$this->setDefaultSort('call_date');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);


	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('rocketweb_netsuite/apilogitem')->getCollection();
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
		
		$this->addColumn('operation', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Operation'),
				'align'     =>  'left',
				'index'     =>  'operation',
		));
		
		$this->addColumn('request', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Operation'),
				'align'     =>  'left',
				'sortable'  =>  false,
				'index'     =>  'request',
				'renderer'  =>  'RocketWeb_Netsuite_Block_Adminhtml_Log_Grid_Renderer_Xml'
		));
		
		$this->addColumn('response', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Operation'),
				'align'     =>  'left',
				'sortable'  =>  false,
				'index'     =>  'response',
				'renderer'  =>  'RocketWeb_Netsuite_Block_Adminhtml_Log_Grid_Renderer_Xml'
		));
		
		$this->addColumn('call_date', array('header' =>  Mage::helper('rocketweb_netsuite')->__('Call date'),
				'align'     =>  'left',
				'type'      =>  'datetime',
				'index'     =>  'call_date',
		));
		
		return parent::_prepareColumns();
	}

    protected function _prepareMassaction() {
        parent::_prepareMassaction();

        $this->setMassactionIdField('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array('label'=>'Delete',
                'url'  => $this->getUrl('*/*/massDeleteApi'),
                'confirm' => Mage::helper('catalog')->__('Are you sure?'))
        );

        return $this;
    }
}