<?php

class Bilna_Promo_Block_Adminhtml_Giftvoucherapplicant_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
		parent::__construct();
		$this->setId('giftvoucherapplicantGrid');
		$this->setDefaultSort('promo_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('bilnapromo/giftvoucherapplicant')->getCollection();

      //CREATE A JOINING AW POINT SPEND ON ORDER
      $collection->getSelect()->joinLeft(	array('giftvoucher' => 'bilna_promo_giftvoucher'),
      		"giftvoucher.id = main_table.promo_id",
      		array('promo'=>'name'));
      $collection->getSelect()->joinLeft(	array('sales_flat_order' => 'sales_flat_order'),
      		"sales_flat_order.increment_id = main_table.order_id",
      		array('status'=>'status'));
      
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
	  
	  $this->addColumn('promo', array(
          'header'    => Mage::helper('promo')->__('Promo'),
          'align'     =>'left',
          'index'     => 'promo',
      ));
	  
	  $this->addColumn('order_id', array(
          'header'    => Mage::helper('promo')->__('Order ID'),
          'align'     =>'left',
          'index'     => 'order_id',
      ));
	  
	  $this->addColumn('status', array(
          'header'    => Mage::helper('promo')->__('Order Status'),
          'align'     =>'left',
          'index'     => 'status',
      ));
	  
	  $this->addColumn('name', array(
          'header'    => Mage::helper('promo')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));

      $this->addColumn('email', array(
          'header'    => Mage::helper('promo')->__('Email'),
          'align'     =>'left',
          'index'     => 'email',
      ));
	  
	  $this->addColumn('address', array(
          'header'    => Mage::helper('promo')->__('Address'),
          'align'     =>'left',
          'index'     => 'address',
      ));
	  
	  $this->addColumn('submit_date', array(
          'header'    => Mage::helper('promo')->__('Submit Date'),
		  'type' => 'datetime',
          'align'     =>'left',
          'index'     => 'submit_date',
      ));
	  
	  $this->addExportType('*/*/exportCsv', Mage::helper('promo')->__('CSV'));
	  
      return parent::_prepareColumns();
  }

}