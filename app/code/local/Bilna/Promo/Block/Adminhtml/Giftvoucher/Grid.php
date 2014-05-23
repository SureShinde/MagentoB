<?php

class Bilna_Promo_Block_Adminhtml_Giftvoucher_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
		parent::__construct();
		$this->setId('giftvoucherGrid');
		$this->setDefaultSort('promo_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('bilnapromo/giftvoucher')->getCollection();

      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('id', array(
          'header'    => Mage::helper('promo')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));
	  
	  $this->addColumn('name', array(
          'header'    => Mage::helper('promo')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));
	  
	  $this->addColumn('value', array(
          'header'    => Mage::helper('promo')->__('Value'),
          'align'     =>'left',
          'index'     => 'value',
      ));

      $this->addColumn('priority', array(
          'header'    => Mage::helper('promo')->__('Priority'),
          'align'     =>'left',
          'index'     => 'priority',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('promo')->__('Status'),
          'align'     =>'left',
          'type'	  => 'options',      	
          'index'     => 'status',
		  'options'	  => array('0'=>'Disabled', '1'=>'Enabled')
      ));
	  
	  $this->addColumn('start_date', array(
          'header'    => Mage::helper('promo')->__('Start Date'),
		  'type' => 'datetime',
          'align'     =>'left',
          'index'     => 'start_date',
      ));
	  
	  $this->addColumn('end_date', array(
          'header'    => Mage::helper('promo')->__('End Date'),
		  'type' => 'datetime',
          'align'     =>'left',
          'index'     => 'end_date',
      ));
	  
      return parent::_prepareColumns();
  }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}