<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Data extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
  {
	parent::__construct();
	$this->setId('bilna_formbuilder_formbuilder_edit_tabs_data');
	$this->setDefaultSort('id');
	$this->setDefaultDir('ASC');
	$this->setSaveParametersInSession(true);
	$this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
		$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
		$collection->getSelect()->reset(Zend_Db_Select::COLUMNS); //hanya menampilkan kolom yg dipilih
		$collection->getSelect()
			->join(array('bff' => 'bilna_formbuilder_input'), 'main_table.form_id = bff.form_id AND main_table.`type` = bff.`name`',array('main_table.record_id', 'main_table.form_id', 'main_table.create_date'));
		$collection->getSelect()
			->joinLeft(array('bfd_name' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_name.record_id AND bfd_name.`type` = 'name' AND bfd_name.form_id = main_table.form_id",array('Name' => 'bfd_name.value'));
		$collection->getSelect()
			->joinLeft(array('bfd_email' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_email.record_id AND bfd_email.`type` = 'email' AND bfd_email.form_id = main_table.form_id",array('Email' => 'bfd_email.value'));
		$collection->getSelect()
			->joinLeft(array('bfd_phone' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_phone.record_id AND bfd_phone.`type` = 'phone' AND bfd_phone.form_id = main_table.form_id",array('Phone' => 'bfd_phone.value'));
		$collection->getSelect()
			->joinLeft(array('bfd_age' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_age.record_id AND bfd_age.`type` = 'age' AND bfd_age.form_id = main_table.form_id",array('Age' => 'bfd_age.value'));
		$collection->getSelect()
			->joinLeft(array('bfd_child' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_child.record_id AND bfd_child.`type` = 'child' AND bfd_child.form_id = main_table.form_id",array('Child' => 'bfd_child.value'));
		$collection->addFieldToFilter('main_table.form_id', (int) $this->getRequest()->getParam('id'));
		$collection->getSelect()->group('main_table.record_id');
		$collection->getSelect()->group('main_table.form_id');
		//$collection->printLogQuery(true); //die;
		$this->setCollection($collection);		 
		return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {	  
	$this->addColumn('Name',
		array(
			'header'=> $this->__('Name'),
			'index' => 'Name',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('Email',
		array(
			'header'=> $this->__('Email'),
			'index' => 'Email',
			'header_css_class'=>'a-center'
	));
	  
	$this->addColumn('Phone',
		array(
			'header'=> $this->__('Phone'),
			'index' => 'Phone',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('Age',
		array(
			'header'=> $this->__('Age'),
			'index' => 'Age',
			'header_css_class'=>'a-center'
	));
	
	$this->addColumn('Child',
		array(
			'header'=> $this->__('Child'),
			'index' => 'Child',
			'header_css_class'=>'a-center'
	));
		
	$this->addExportType('*/*/exportCsv', Mage::helper('bilna_formbuilder')->__('CSV'));
	  
  return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('form_id');
    $this->getMassactionBlock()->setFormFieldName('formbuilder');

    $this->getMassactionBlock()->addItem('delete',
      array(
        'label' 	=> Mage::helper('bilna_formbuilder')->__('Delete'),
        'url' 		=> $this->getUrl('*/*/massDelete'),
        'confirm' => Mage::helper('bilna_formbuilder')->__('Are you sure?')
      ));
	}

  //Grid with Ajax Request
  public function getGridUrl() 
	{
    return $this->getUrl('*/*/gridData', array ('_current' => true));
  }

  public function getRowUrl($row)
  {
    return $this->getUrl('*/*/edit', array('record_id' => $row->getRecordId(),'form_id' => $row->getFormId()));
  }	
}
