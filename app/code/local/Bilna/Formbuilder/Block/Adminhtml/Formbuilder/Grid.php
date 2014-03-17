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
		/*		
		$collection = Mage::getResourceModel($this->_getCollectionClass());
		$collection->getSelect()->join('bilna_form', 'main_table.form_id = bilna_form.id',array('title'));
		$this->setCollection($collection);
		$collection->printLogQuery(true);
		*/
		
		$collection = Mage::getResourceModel($this->_getCollectionClass());
		$collection->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id',array('form_id'));
		$collection->getSelect()->join('bilna_formbuilder_data', 'bilna_formbuilder_input.form_id = bilna_formbuilder_data.form_id AND bilna_formbuilder_input.`group` = bilna_formbuilder_data.type',array('record_id','type','value','create_date'));
		$this->setCollection($collection);
		//$collection->printLogQuery(true);
		 
		return parent::_prepareCollection();
    }

  protected function _prepareColumns()
  {	

	$this->addColumn('id_check', 
		array(
		    	'type'     => 'checkbox',
		    	'align'    => 'center',
		    	'index'    => 'id',
		    	'field_name' => 'checkbox_name',
		    	'values'   => array(1,2),
			'header_css_class'=>'a-center'

	));

	$this->addColumn('id', 
		array(
			'header'=> $this->__('ID'),
			'align' =>'right',
			'width' => '50px',
			'index' => 'id',
			'header_css_class'=>'a-center'
	));			
	
	$combobox = $this->getComboForm();
	
	$this->addColumn('title',
		array(
			'header' =>Mage::helper('bilna_formbuilder')->__('Title'),
			'align' =>'right',
			'width' => '30px',
			'index' => 'title',
			'type'  => 'options',
			'options' => $combobox,
			'header_css_class'=>'a-center'
	));
	  
	$this->addColumn('name',
		array(
			'header'=> $this->__('Name'),
			'index' => 'name',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('email',
		array(
			'header'=> $this->__('Email'),
			'index' => 'email',
			'header_css_class'=>'a-center'
	));
	  
	$this->addColumn('phone',
		array(
			'header'=> $this->__('Phone'),
			'index' => 'phone',
			'header_css_class'=>'a-center'
	));
	  
	$this->addColumn('form_id',
		array(
			'header'=> $this->__('Form ID'),
			'align' => 'right',
			'index' => 'form_id',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('record_id',
		array(
			'header'=> $this->__('Record ID'),
			'align' => 'right',
			'index' => 'record_id',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('type',
		array(
			'header'=> $this->__('Type'),
			'index' => 'type',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('value',
		array(
			'header'=> $this->__('Value'),
			'index' => 'value',
			'header_css_class'=>'a-center'
	));
	
	$this->addColumn('create_date',
		array(
			'header'=> $this->__('Submit Date'),
			'type' => 'datetime',
			'index' => 'create_date',
			'header_css_class'=>'a-center'
	));
		
	$this->addExportType('*/*/exportCsv', Mage::helper('bilna_formbuilder')->__('CSV'));
	  
      return parent::_prepareColumns();
  }
  
	private function getComboForm() {
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql        = "select title from bilna_formbuilder_form group by title";
		$rows       = $connection->fetchAll($sql);
		$result = array ();
				
		foreach ($rows as $key=>$row) {
			$result[$row['title']] = $row['title'];
		}
		
		return $result;
	}
}
