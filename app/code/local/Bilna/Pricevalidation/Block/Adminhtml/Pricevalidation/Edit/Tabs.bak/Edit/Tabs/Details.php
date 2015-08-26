<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Tabs_Details extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
		$formbuilder	= Mage::registry('formbuilder_form');
    $form 				= new Varien_Data_Form();
    $form->setUseContainer(true);
		$this->setForm($form);
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('Data Detail')));

		$inputs = Mage::getModel('bilna_formbuilder/input')->getCollection();
		$inputs->getSelect();
		//$inputs->addFieldToFilter('main_table.record_id', (int) $this->getRequest()->getParam('record_id'));
		$inputs	->addFieldToFilter('main_table.form_id', (int) $this->getRequest()->getParam('form_id'));
		$inputs->getSelect()->order(array('required DESC'));
		$inputs->getSelect()->group('group');

		$i = 0;
		foreach($inputs as $input){
			if($i == 0){
				$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
				$collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('record_id'=>'record_id', $input["group"]=>'value', 'create_date'=>'create_date'));
				$collection->addFilterToMap($input["group"], 'main_table.value');
				$collection->addFieldToFilter('main_table.record_id', (int) $this->getRequest()->getParam('record_id'));
				$collection->addFieldToFilter('main_table.form_id', (int) $this->getRequest()->getParam('form_id'));
				$collection->addFieldToFilter('main_table.`type`', $input["group"]);
			}else{
				$collection->getSelect()->joinLeft(	array("input_".$input["group"] => "bilna_formbuilder_data"), 
										"main_table.record_id = input_".$input["group"].".record_id AND main_table.form_id = input_".$input["group"].".form_id AND input_".$input["group"].".`type` ='".$input["group"]."'", array($input["group"]=>'value'));
				$collection->addFilterToMap($input["group"], 'input_'.$input["group"].'.value');
				$collection->addFilterToMap('create_date', 'input_'.$input["group"].'.create_date');
			}
		
			$i++;
		}

		$this->setCollection($collection);
		$data = $collection->getFirstItem();

  	foreach($inputs as $input){
		//$fieldset->addField($input["group"], 'label',
		$fieldset->addField($input["group"], 'text',
			array(
				'label'=> $input["title"],
				'name' => $input["group"],
				'value'=> $data[$input["group"]],
		));
  	}
		
		$outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

		//$fieldset->addField('create_date', 'label', array(
		$fieldset->addField('create_date', 'date', array(
		   'label'	=> $this->__('Create Date'),
		   'name' 	=> 'create_date',
			 'title'	=> $this->__('Create Date'),
		   'value'	=> $data["create_date"],
			 'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
			 'format'	=> $outputFormat,
       'time' 	=> true,
    ));

		$form->setValues($data);
    return parent::_prepareForm();
	}
}
