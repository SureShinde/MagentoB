<?php

class Bilna_Promo_Block_Adminhtml_Gimmickevent_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
		parent::__construct();
		$this->setId('gimmickeventGrid');
		$this->setDefaultSort('promo_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
		$collection = Mage::getModel('bilnapromo/gimmickeventapplicant')->getCollection();
		$collection->getSelect()->joinLeft(	array('bge' => 'bilna_gimmick_event'),
							        		"bge.id = main_table.event_id",
							        		array('name'));

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
	  
	  $this->addColumn('order_increment_id', array(
          'header'    => Mage::helper('promo')->__('Order ID'),
          'align'     =>'left',
          'index'     => 'order_increment_id',
      ));

      $this->addColumn('user_email', array(
          'header'    => Mage::helper('promo')->__('User Email'),
          'align'     =>'left',
          'index'     => 'user_email',
      ));
	  
	  $this->addColumn('products', array(
          'header'    => Mage::helper('promo')->__('Product(s)'),
          'align'     =>'left',
          'index'     => 'products',
      ));

      $this->addColumn('order_date', array(
          'header'    => Mage::helper('promo')->__('Date'),
		  'type' => 'datetime',
          'align'     =>'left',
          'index'     => 'order_date',
      ));
	  
      return parent::_prepareColumns();
  }
}