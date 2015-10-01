<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Tabs_Detail extends Mage_Adminhtml_Block_Widget_Form
{
	protected $inputId;

   	protected function _prepareForm()
   	{
		//$formbuilder	= Mage::registry('formbuilder_form');
		$this->inputId = (int) $this->getRequest()->getParam('id');
	    //$form = new Varien_Data_Form();
	    $form = new Varien_Data_Form(array('id' => 'edit_input',
			'action' => $this->getUrl('*/*/saveInput', ['id' => $this->inputId]),
			'method' => 'post', 
			'enctype' => 'multipart/form-data'));
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('Inputs Detail')));
		
		$data = Mage::getModel('bilna_formbuilder/input')->findByParent($this->inputId);

		//$fieldset->addField('name', 'label', array(
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
			'value' => $data['value']
		));

		$fieldset->addField('required', 'select', array(
		   'label'     => 'Required',
		   'name'      => 'required',
		   //'value'   => $data["required"],
       //'note'    => Mage::helper('bilna_formbuilder')->__('Status Note:</br>0=Enabled</br>1=Disabled'),
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
		   //'value'   => $data["unique"],
       //'note'    => Mage::helper('bilna_formbuilder')->__('Status Note:</br>0=Enabled</br>1=Disabled'),
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

	private function fieldTypeOptions()
	{
		return $this->renderOptions([
			"text",
			"textarea",
			"radio",
			"checkbox",
			"dropdown",
			"multiple",
			"hidden",
			"date",
			"datetime"
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
