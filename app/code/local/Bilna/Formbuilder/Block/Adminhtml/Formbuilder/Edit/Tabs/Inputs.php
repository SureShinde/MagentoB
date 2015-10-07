<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Inputs extends Mage_Adminhtml_Block_Widget_Grid
{	
	protected $formId;

	public function __construct()
  	{
		parent::__construct();
		$this->setId('bilna_formbuilder_formbuilder_edit_tabs_inputs');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);

		$this->formId = $this->getRequest()->getParam('id');
  	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('bilna_formbuilder/input')->getCollection();
		$collection->addFieldToFilter('main_table.form_id', (int) $this->getRequest()->getParam('id'));

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('name',
			array(
				'header'=> $this->__('Name field'),
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
				'header'	=> $this->__('Required'),
				'index' 	=> 'required',
				'type'		=> 'options',
				'options' => array(
						'1' 	=> 'Yes',
						'0' 	=> 'No',),
				'header_css_class'=>'a-center'
		));

		$this->addColumn('unique',
			array(
				'header'	=> $this->__('Unique'),
				'index' 	=> 'unique',
				'type'		=> 'options',
				'options' => array(
						'1' 	=> 'Yes',
						'0' 	=> 'No',),
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
	  	parent::_prepareMassaction();
	}

	//Grid with Ajax Request
	public function getGridUrl() 
	{
		return $this->getUrl('*/*/gridInputs', array ('_current' => true));
	}

  	public function getRowUrl($row)
  	{
		return $this->getUrl('*/*/editInput', array('id' => $row->getId(), 'form_id' => $this->formId));
  	}	 
}
