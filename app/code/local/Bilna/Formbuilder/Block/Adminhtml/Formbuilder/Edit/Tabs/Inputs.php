<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Inputs extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
  {
		parent::__construct();
		$this->setId('bilna_formbuilder_formbuilder_grid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
  }

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('bilna_formbuilder/input')->getCollection();
		$collection->getSelect()
		->joinInner(array('bff' => 'bilna_formbuilder_form'), 'main_table.form_id = bff.id',array('bff.title')); 
		//$collection->printLogQuery(true); //die;
		$this->setCollection($collection);		 
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
  {
	$this->addColumn('name',
		array(
			'header'=> $this->__('Name'),
			'index' => 'name',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('group',
		array(
			'header'=> $this->__('Group'),
			'index' => 'group',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('title',
		array(
			'header'=> $this->__('Title'),
			'index' => 'title',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('type',
		array(
			'header'=> $this->__('Type'),
			'index' => 'type',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('required',
		array(
			'header'=> $this->__('Required'),
			'index' => 'required',
			'type'	=> 'options',
			'options' => array(
					'0' => 'Yes',
					'1' => 'No',),
			'header_css_class'=>'a-center'
	));

	$this->addColumn('unique',
		array(
			'header'=> $this->__('Unique'),
			'index' => 'unique',
			'type'	=> 'options',
			'options' => array(
					'0' => 'Yes',
					'1' => 'No',),
			'header_css_class'=>'a-center'
	));

	$this->addColumn('order',
		array(
			'header'=> $this->__('Order'),
			'index' => 'order',
			'header_css_class'=>'a-center'
	));
	  
  return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('id');
    $this->getMassactionBlock()->setFormFieldName('formbuilder');

    $this->getMassactionBlock()->addItem('delete',
      array(
        'label' => Mage::helper('bilna_formbuilder')->__('Delete'),
        'url' => $this->getUrl('*/*/massDelete'),
        'confirm' => Mage::helper('bilna_formbuilder')->__('Are you sure?')
      ));
	}

  //Grid with Ajax Request
  public function getGridUrl() 
	{
    return $this->getUrl('*/*/grid', array ('_current' => true));
  }

  public function getRowUrl($row)
  {
    return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }	 
}
