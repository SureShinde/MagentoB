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

    protected function _prepareCollection()
    {

		$collection = Mage::getModel('bilna_formbuilder/form')->getCollection();

    //$collection->getSelect()->reset(Zend_Db_Select::COLUMNS); //hanya menampilkan kolom yg dipilih

		/*$collection->getSelect()
			->join(array('bff' => 'bilna_formbuilder_form'), 'bff.id = main_table.form_id', array(
					'bff.id', 
					'bff.title',
					'bff.url', 
					'bff.active_from', 
					'bff.active_to', 
					'bff.status'));*/

		//$collection->getSelect()->group('bff.title');

		//$collection->printLogQuery(true); die;
		$this->setCollection($collection);		 
		return parent::_prepareCollection();
    }

  protected function _prepareColumns()
  {
	
	$combobox = $this->getComboForm();

  /*$this->addColumn('id', array(
      'header' =>Mage::helper('bilna_formbuilder')->__('ID'),
      'align' => 'right',
      'width' => '50px',
      'index' => 'id',
  ));*/
	
	$this->addColumn('title',
		array(
			'header' =>Mage::helper('bilna_formbuilder')->__('Title'),
			//'align' =>'right',
			//'width' => '30px',
			'index' => 'title',
			'type'  => 'options',
			'options' => $combobox,
			'header_css_class'=>'a-center'
	));
	  
	/*$this->addColumn('Name',
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

	$this->addColumn('Comment',
		array(
			'header'=> $this->__('Comment'),
			'index' => 'Comment',
			'header_css_class'=>'a-center'
	));
	
	$this->addColumn('create_date',
		array(
			'header'=> $this->__('Submit Date'),
			'type' => 'date',
			'index' => 'create_date',
			'header_css_class'=>'a-center'
	));*/

	$this->addColumn('url',
		array(
			'header'=> $this->__('URL'),
			'index' => 'url',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('active_from',
		array(
			'header'=> $this->__('Active From'),
			'type' => 'datetime',
			'index' => 'active_from',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('active_to',
		array(
			'header'=> $this->__('Active To'),
			'type' => 'datetime',
			'index' => 'active_to',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('status',
		array(
			'header'=> $this->__('Status'),
			'index' => 'status',
			'type'  => 'options',
      'options' => array(
				'0'=>'Enabled',
				'1'=>'Disabled'),
			'header_css_class'=>'a-center'
	));
		
	//$this->addExportType('*/*/exportCsv', Mage::helper('bilna_formbuilder')->__('CSV'));
	  
      return parent::_prepareColumns();
  }
  
	private function getComboForm() {
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql        = "select title from bilna_formbuilder_form";
		$rows       = $connection->fetchAll($sql);
		$result = array ();
				
		foreach ($rows as $key=>$row) {
			$result[$row['title']] = $row['title'];
		}
		
		return $result;
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
