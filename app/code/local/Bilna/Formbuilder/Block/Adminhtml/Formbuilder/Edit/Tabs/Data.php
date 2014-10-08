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
		$inputs = Mage::getModel('bilna_formbuilder/input')->getCollection();
		$inputs->getSelect();
		$inputs	->addFieldToFilter('main_table.form_id', (int) $this->getRequest()->getParam('id'));
		$inputs->getSelect()->order(array('required DESC'));
		$inputs->getSelect()->group('group');

		$i = 0;
		foreach($inputs as $input){
			if($i == 0){
				$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
				$collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('record_id'=>'record_id', $input["group"]=>'value', 'create_date'=>'create_date'));
				$collection->addFilterToMap($input["group"], 'main_table.value');
				$collection->addFieldToFilter('main_table.form_id', (int) $this->getRequest()->getParam('id'));
				$collection->addFieldToFilter('main_table.`type`', $input["group"]);
			}else{
				$collection->getSelect()->joinLeft(	array("input_".$input["group"] => "bilna_formbuilder_data"), 
										"main_table.record_id = input_".$input["group"].".record_id AND main_table.form_id = input_".$input["group"].".form_id AND input_".$input["group"].".`type` ='".$input["group"]."'", array($input["group"]=>'value'));
				$collection->addFilterToMap($input["group"], 'input_'.$input["group"].'.value');
				$collection->addFilterToMap('create_date', 'input_'.$input["group"].'.create_date');
			}
		
			$i++;
		}
		
		//Zend_Debug::Dump($collection->printLogQuery(true)); die;
		$this->setCollection($collection);		 
		return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
  	$inputs = Mage::getModel('bilna_formbuilder/input')->getCollection();
  	$inputs->getSelect();
  	$inputs->addFieldToFilter('main_table.form_id', (int) $this->getRequest()->getParam('id'));
		$inputs->getSelect()->order(array('required DESC'));
  	$inputs->getSelect()->group('group');
  	
  	foreach($inputs as $input){
		$this->addColumn($input["title"],
			array(
				'header'=> $input["title"],
				'index' => $input["group"],
				'header_css_class'=>'a-center'
		));
  	}
	  
		$outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

		$this->addColumn("create_date",
			array(
				'header'=> $this->__("Created Datee"),
				'type' 	=> 'date',
				'index' => "create_date",
			  'format'=> $outputFormat,
        //'time' 	=> true,
				'header_css_class'=>'a-center'
		));

		/*$fieldset->addField('create_date', 'date', array(
		   'label'	=> $this->__('Create Date'),
		   'name' 	=> 'create_date',
			 'title'	=> $this->__('Create Date'),
		   'value'	=> $data["create_date"],
			 'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
			 'format'	=> $outputFormat,
       'time' 	=> true,
    ));*/
		
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
    return $this->getUrl('*/*/gridData', array ('_current' => 'true'));
  }

  public function getRowUrl($row)
  {
    return $this->getUrl('*/*/editData', array('record_id' => $row->getRecordId(), 'form_id' => $this->getRequest()->getParam('id')));
  }	
}
