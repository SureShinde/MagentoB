<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Tabs_Detail extends Mage_Adminhtml_Block_Widget_Form
{
	protected $inputId;

   	protected function _prepareForm()
   	{

   		$helper = Mage::helper('core');
		$this->inputId = (int) $this->getRequest()->getParam('id');
	    $form = new Varien_Data_Form(array('id' => 'edit_input',
			'action' => $this->getUrl('*/*/saveInput', ['id' => $this->inputId]),
			'method' => 'post', 
			'enctype' => 'multipart/form-data'));
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('Inputs Detail')));
		
		$data = Mage::getModel('bilna_formbuilder/input')->findByParent($this->inputId);

		$fieldset->addField('name', 'text', array(
		   'label'     => 'Name',
		   'name'      => 'name',
		   'value'     => $data["name"],
    	));

		$fieldset->addField('group', 'text', array(
		   'label'     => 'Group',
		   'name'      => 'group',
		   'value'     => $data["group"],
    	));

		$fieldset->addField('title', 'text', array(
		   'label'     => 'Title',
		   'name'      => 'title',
		   'value'     => $data["title"],
    	));

		$fieldset->addField('type', 'select', array(
			'label'		=> 'Type',
			'name'		=> 'type',
			'onchange' => 'changeValue(this.value)',
			'value'		=> $data["type"],
			'values'	=> $this->fieldTypeOptions()
		));

		$fieldset->addField('dbtype', 'select', array(
			'label'	=> 'DbType',
			'name'	=> 'dbtype',
			'values' => $this->fieldDbTypeOptions()
		));

		$fieldset->addField('dbtype_length', 'text', array(
			'label' => 'DbType Length (if needed)',
			'name' => 'dbtype_length',
		));

		 $fieldset->addField('value', 'textarea', array(
			'label' => 'Value',
			'name' => 'value',
			'value' => $data['value'],
			'container_id' => 'text_value',
		));

		$fieldset->addType('customtype', 'Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Form_Renderer_Fieldset_Customtype');
		$fieldset->addField('grid_value', 'customtype', array(
		 	'label' => 'Value',
		 	'name' => 'grid_value',
			'container_id' => 'grid_value',
		));
		$asdf = $this->isJson($data['value']) ? Mage::helper('core')->jsonDecode($data['value']) : $data['value'];
		Mage::register('grid_value', $asdf);

		/*
		$fieldset->addField('date_value', 'date', array(
	        'label' => 'Value',
	        'tabindex' => 1,
	        'image' => $this->getSkinUrl('images/grid-cal.gif'),
	        'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
	        'value' => $data['value'],
	        'container_id' => 'date_value'
        ));
       	*/       

		$fieldset->addField('required', 'select', array(
		   'label'     => 'Required',
		   'name'      => 'required',
		   'values'    => array(
            	array(
                	'value' => 1,
                	'label' => Mage::helper('bilna_formbuilder')->__('Yes'),
             	),
             	array(
                 	'value' => 0,
                 	'label' => Mage::helper('bilna_formbuilder')->__('No'),
             	),
         	),
    	));

		$fieldset->addField('unique', 'select', array(
		   'label'     => 'Unique',
		   'name'      => 'unique',
		   'values'    => array(
             	array(
                 	'value' => 1,
                 	'label' => Mage::helper('bilna_formbuilder')->__('Yes'),
             	),
             	array(
                 	'value' => 0,
                 	'label' => Mage::helper('bilna_formbuilder')->__('No'),
             	),
         	),
    	));

		$field = $fieldset->addField('stores', 'multiselect', array(
            'label' => Mage::helper('rating')->__('Visible In'),
            'name' => 'stores[]',
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

		$fieldset->addField('helper_message', 'text', array(
    		'label' => 'Helper message',
    		'name' => 'helper_message',
    		'value' => $data['helper_message']
    	));

    	$fieldset->addField('validation', 'text', array(
    		'label' => 'Validation',
    		'name' => 'validation',
    		'value' => $data['validation']
    	));

		$fieldset->addField('order', 'text', array(
		   'label'     => 'Order',
		   'name'      => 'order',
		   'value'     => $data["order"],
    	));

		$dataType = $data['dbtype'];
		$data['dbtype'] = $this->renderDbType($dataType, 'type');
		$data['dbtype_length'] = $this->renderDbType($dataType, 'length');

		$form->setValues($data);
		$form->setUseContainer(true);
		$this->setForm($form);
    return parent::_prepareForm();
	}

	private function isJson($string) {
 		json_decode($string);
 		return (json_last_error() == JSON_ERROR_NONE);
	}

	private function fieldTypeOptions()
	{
		//text|textarea|radio|checkbox|dropdown|multiple|hidden|date|datetime|terms|ref
		return $this->renderOptions([
			"text",
			"textarea",
			"radio",
			"checkbox",
			//"checkbox_multi",
			"dropdown",
			"multiple",
			"hidden",
			"date",
            //"dob",
			"datetime",
			"terms",
			"ref"
		]);
	}

	private function fieldDbTypeOptions()
	{
		return $this->renderOptions([
			'text',
			"int",
			"varchar",
			"date",
			"datetime"
		]);
	}

	private function renderOptions(array $data)
	{
		$options = [];
		$helper = Mage::helper('bilna_formbuilder');
		foreach ($data as $type) {
			$options[] = array(
					'value' => $type,
					'label' => $helper->__(ucwords($type))
				);
		}

		return $options;
	}
	/**
	 * Render datatype
	 * @throw Exception
	 */
	private function renderDbType($dataType, $dest)
	{
		if(in_array($dest, ['length','type'])) {
			return $this->{$dest}($dataType);
		}
	}

	private function type($data) {
		return preg_replace("/\(([^)]+)\)/", "", $data);
	}

	private function length($data) {
		$length = 0;
		preg_match_all("/\(([^)]+)\)/", $data, $matches);
		try{
			$length = $matches[1][0];
		} catch(Exception $e) {}
		return $length;
	}
}
