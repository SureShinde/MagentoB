<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
	parent::__construct();
	$this->setId('bilna_formbuilder_formbuilder_grid');
	$this->setDefaultSort('id');
	$this->setDefaultDir('ASC');
	$this->setSaveParametersInSession(true);
	$this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
	$collection = Mage::getModel('bilna_formbuilder/form')->getCollection();
	//$collection->printLogQuery(true); die;
	$this->setCollection($collection);		 
	return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {	
	$combobox = $this->getComboForm();	
	$this->addColumn('title',
		array(
			'header'	=>Mage::helper('bilna_formbuilder')->__('Title'),
			//'align' =>'right',
			//'width' => '30px',
			'index' 	=> 'title',
			'type'  	=> 'options',
			'options' => $combobox,
			'header_css_class'=>'a-center'
	));	  

	$this->addColumn('url',
		array(
			'header'=> $this->__('URL'),
			'index' => 'url',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('active_from',
		array(
			'header'=> $this->__('Active From'),
			'type' 	=> 'datetime',
			'index' => 'active_from',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('active_to',
		array(
			'header'=> $this->__('Active To'),
			'type' 	=> 'datetime',
			'index' => 'active_to',
			'header_css_class'=>'a-center'
	));

	$this->addColumn('status',
		array(
			'header'	=> $this->__('Status'),
			'index' 	=> 'status',
			'type'  	=> 'options',
      'options' => array(
				'0'			=>'Enabled',
				'1'			=>'Disabled'),
			'header_css_class'=>'a-center'
	));
	  
  return parent::_prepareColumns();
  }
  
	private function getComboForm() {
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql        = "select title from bilna_formbuilder_form";
		$rows       = $connection->fetchAll($sql);
		$result 		= array ();
				
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
        'label'		=> Mage::helper('bilna_formbuilder')->__('Delete'),
        'url' 		=> $this->getUrl('*/*/massDelete'),
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
