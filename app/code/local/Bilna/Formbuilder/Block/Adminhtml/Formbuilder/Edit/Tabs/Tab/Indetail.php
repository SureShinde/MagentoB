<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Tab_Indetail extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
		$formbuilder = Mage::registry('formbuilder_form');
        $form = new Varien_Data_Form();
        $form->setUseContainer(true);
		$this->setForm($form);
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('General')));

		$collection = Mage::getModel('bilna_formbuilder/form')->getCollection();
		//$collection->getSelect()->join(array('bff' => 'bilna_formbuilder_form'), 'main_table.id = bff.id', array('bff.title'));
		$collection->addFieldToFilter('main_table.id', (int) $this->getRequest()->getParam('id'));
		//$collection->addFieldToFilter('main_table.record_id', (int) $this->getRequest()->getParam('id'));

		$this->setCollection($collection);

		$data = $collection->getFirstItem();

		/*foreach($data->getData() as $key=>$value){
		echo $key."---".$value."<br>";
		}die;*/

		$fieldset->addField('title', 'label', array(
         'label'     => 'Title',
         //'class'   => 'required-entry',
         //'required'=> true,
         'name'      => 'title',
         'value'     => $data["title"],
         //'note'    => Mage::helper('bilna_formbuilder')->__('Nama form / promo'),
    ));

		$fieldset->addField('url', 'label', array(
         'label'     => 'URL',
         //'class'   => 'required-entry',
         //'required'=> true,
         'name'      => 'url',
         'value'     => $data["url"],
         //'note'    => Mage::helper('bilna_formbuilder')->__('The name of the example.'),
    ));

		$fieldset->addField('active_from', 'label', array(
         'label'     => 'Aktif Dari',
         //'class'   => 'required-entry',
         //'required'=> true,
         'name'      => 'active_from',
         'value'     => $data["active_from"],
         //'note'    => Mage::helper('bilna_formbuilder')->__('The name of the example.'),
    ));

		$fieldset->addField('active_to', 'label', array(
         'label'     => 'Aktif Sampai',
         //'class'   => 'required-entry',
         //'required'=> true,
         'name'      => 'active_to',
         'value'     => $data["active_to"],
         //'note'    => Mage::helper('bilna_formbuilder')->__('The name of the example.'),
    ));

		$fieldset->addField('status', 'label', array(
         'label'     => 'Status',
         //'class'   => 'required-entry',
         //'required'=> true,
         'name'      => 'status',
         'value'     => $data["status"],
         'note'      => Mage::helper('bilna_formbuilder')->__('Status Note:</br>0=Enabled</br>1=Disabled'),
    ));

    return parent::_prepareForm();
	}
}
