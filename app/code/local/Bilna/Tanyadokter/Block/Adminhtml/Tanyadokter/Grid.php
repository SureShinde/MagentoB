<?php

class Bilna_Tanyadokter_Block_Adminhtml_Tanyadokter_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
	parent::__construct();
	$this->setDefaultSort('id');
	$this->setId('bilna_tanyadokter_tanyadokter_grid');
	$this->setDefaultDir('ASC');
	$this->setSaveParametersInSession(true);
  }
  
	protected function _getCollectionClass()
	{
		return 'bilna_tanyadokter/tanyadokter_collection';
	}

    protected function _prepareCollection()
    {
		$collection = Mage::getResourceModel($this->_getCollectionClass());
		$this->setCollection($collection);
		 
		return parent::_prepareCollection();
    }

  protected function _prepareColumns()
  {

	$this->addColumn('id',
		array(
			'header'=> $this->__('ID'),
			'align' =>'right',
			'width' => '50px',
			'index' => 'id'
	));
	  
	$this->addColumn('name',
		array(
			'header'=> $this->__('Name'),
			'index' => 'name'
	));

	$this->addColumn('email',
		array(
			'header'=> $this->__('Email'),
			'index' => 'email'
	));
	  
	$this->addColumn('phone',
		array(
			'header'=> $this->__('Phone'),
			'index' => 'phone'
	));
	  
	$this->addColumn('comment',
		array(
			'header'=> $this->__('Comment'),
			'index' => 'comment'
	));
	
	$this->addColumn('submit_date',
		array(
			'header'=> $this->__('Submit Date'),
			'type' => 'datetime',
			'index' => 'submit_date'
	));
		
	$this->addExportType('*/*/exportCsv', Mage::helper('bilna_tanyadokter')->__('CSV'));
	  
      return parent::_prepareColumns();
  }
  
  // public function getGridUrl()
    // {
        // return $this->getUrl('*/*/grid', array('_current'=>true));
    // }
	
    // public function getRowUrl($row)
    // {
        // return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    // }

}