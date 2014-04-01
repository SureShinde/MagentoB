<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

	   public function __construct()
   {
        parent::__construct();
        $this->setId('formbuilder_tabs');
        //$this->setDestElementId('edit_form');
        //$this->setTitle('Form Information');
				$this->setTitle(Mage::helper('bilna_formbuilder')->__('Form Information'));
    }

   protected function _prepareForm()
   {

			$form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data'));
			$form->setUseContainer(true);
			$this->setForm($form);
      return parent::_prepareForm();

        //$form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data'));
        //$form->setUseContainer(true);
        //$this->setForm($form);
        return parent::_prepareForm();

     /* $fieldset = $form->addFieldset('formbuilder_form',
          array(
								'legend' => Mage::helper('bilna_formbuilder')->__('Form Details'),
								)
																	);
			$form->setHtmlIdPrefix('formbuilder_');

      $fieldset->addField('Name', 'text',
             array(
                'name' => 'name',
                'label' => Mage::helper('bilna_formbuilder')->__('Name'),
                'title' => Mage::helper('bilna_formbuilder')->__('Name'),
								'readonly' => true,
								'index' => 'Name',
								'value' => 'Name',
          ));*/

        //$form->setValues($deal->getData());
        //$this->setForm($form);
        //return parent::_prepareForm();
		}
}
