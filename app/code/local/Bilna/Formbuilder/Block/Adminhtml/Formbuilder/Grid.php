<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
	parent::__construct();
	$this->setDefaultSort('id');
	$this->setId('bilna_formbuilder_formbuilder_grid');
	$this->setDefaultDir('ASC');
	$this->setSaveParametersInSession(true);
  }
  
	protected function _getCollectionClass()
	{
		return 'bilna_formbuilder/formbuilder_collection';
	}

    protected function _prepareCollection()
    {
		$collection = Mage::getResourceModel($this->_getCollectionClass());
		//$collection->printLogQuery(true); die;
		//SELECT `main_table`.* FROM `bilna_form_data` AS `main_table`
		$collection->getSelect()->join('bilna_form', 'main_table.form_id = bilna_form.id',array('title'));
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
			
	// $resource = Mage::getSingleton('core/resource');
    // $read = $resource->getConnection('core_read');
    // $select = $read->select()
        // ->from(array('f' => 'bilna_form'), array('id' => 'f.id'))
        // ->joinInner(array('fd' => 'bilna_form_data'), 'f.id=fd.form_id');
	// $form = $read->fetchRow($select);
	// //Zend_Debug::Dump($form); die;
	
	$this->addColumn('title',
		array(
			//'header'=> $this->__('Type'),
			'header' =>Mage::helper('bilna_formbuilder')->__('Title'),
			'align' =>'right',
			'width' => '30px',
			'index' => 'title',
			//'type'  => 'options',
			//'options' => $this,
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
		
	$this->addExportType('*/*/exportCsv', Mage::helper('bilna_formbuilder')->__('CSV'));
	  
      return parent::_prepareColumns();
  }

}