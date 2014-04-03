<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
	parent::__construct();
	$this->setDefaultSort('form_id');
	$this->setId('bilna_formbuilder_formbuilder_grid');
	$this->setDefaultDir('ASC');
	$this->setSaveParametersInSession(true);
  }
  
	//protected function _getCollectionClass()
	//{
	//	return 'bilna_formbuilder/formbuilder_collection';
	//}

    protected function _prepareCollection()
    {
		/*Formbuilder lama	
		$collection = Mage::getResourceModel($this->_getCollectionClass());
		$collection->getSelect()->join('bilna_form', 'main_table.form_id = bilna_form.id',array('title'));
		$this->setCollection($collection);
		$collection->printLogQuery(true);
		*/

		/*SQL Query
		SELECT bfd.record_id ID, bff.title FORM, bfd_name.value `NAME`, bfd_email.value `EMAIL`, 
		bfd_phone.value `PHONE`, bfd_comment.value `COMMENT`, bfd.create_date
		FROM bilna_formbuilder_data  bfd
		INNER JOIN bilna_formbuilder_form bff ON bfd.form_id = bff.id
		LEFT JOIN bilna_formbuilder_data bfd_name ON bfd.record_id = bfd_name.record_id AND bfd_name.type = 'g-name'
		LEFT JOIN bilna_formbuilder_data bfd_email ON bfd.record_id = bfd_email.record_id AND bfd_email.type = 'g-email'
		LEFT JOIN bilna_formbuilder_data bfd_phone ON bfd.record_id = bfd_phone.record_id AND bfd_phone.type = 'g-phone'
		LEFT JOIN bilna_formbuilder_data bfd_comment ON bfd.record_id = bfd_comment.record_id 
		AND bfd_comment.type = 'g-comment'
		GROUP BY bfd.record_id, bfd.form_id
		*/

		//$collection = Mage::getResourceModel($this->_getCollectionClass()); //Formbuilder lama
		$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
    $collection->getSelect()->reset(Zend_Db_Select::COLUMNS); //hanya menampilkan kolom yg dipilih
		$collection->getSelect()
			->join(array('bff' => 'bilna_formbuilder_form'), 'main_table.form_id = bff.id',array('main_table.record_id', 'main_table.form_id', 'bff.title','main_table.create_date'));
		$collection->getSelect()
			->joinLeft(array('bfd_name' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_name.record_id AND bfd_name.type = 'name'",array('Name' => 'bfd_name.value'));
		$collection->getSelect()
			->joinLeft(array('bfd_email' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_email.record_id AND bfd_email.type = 'email'",array('Email' => 'bfd_email.value'));
		$collection->getSelect()
			->joinLeft(array('bfd_phone' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_phone.record_id AND bfd_phone.type = 'phone'",array('Phone' => 'bfd_phone.value'));
		$collection->getSelect()
			->joinLeft(array('bfd_comment' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_comment.record_id AND bfd_comment.type = 'comment'",array('Comment' => 'bfd_comment.value'));
		$collection->getSelect()
			->joinLeft(array('bfd_birth' => 'bilna_formbuilder_data'), "main_table.record_id = bfd_birth.record_id AND bfd_birth.type = 'birth'",array('Birth' => 'bfd_birth.value'));
		$collection->getSelect()->group('main_table.record_id');
		$collection->getSelect()->group('main_table.form_id');
		//$collection->printLogQuery(true); //die;
		$this->setCollection($collection);		 
		return parent::_prepareCollection();
    }

  protected function _prepareColumns()
  {
	
	$combobox = $this->getComboForm();
	
	$this->addColumn('title',
		array(
			'header' =>Mage::helper('bilna_formbuilder')->__('Form'),
			'align' =>'right',
			'width' => '30px',
			'index' => 'title',
			'type'  => 'options',
			'options' => $combobox,
			'header_css_class'=>'a-center'
	));
	  
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

	$this->addColumn('age',
		array(
			'header'=> $this->__('Age'),
			'index' => 'age'
	));

	$this->addColumn('child',
		array(
			'header'=> $this->__('Child'),
			'index' => 'child'
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

    protected function _prepareMassaction()
    {
      $this->setMassactionIdField('form_id');
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
      return $this->getUrl('*/*/edit', array('record_id' => $row->getRecordId(),'form_id' => $row->getFormId()));
    }	  
}
