<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
		$formbuilder = Mage::registry('formbuilder_form');
    $form = new Varien_Data_Form();
    /*$form = new Varien_Data_Form(array(
        'id' => 'edit_form',
        'action' => $this->getUrl('*//*/save', array('id' => $this->getRequest()->getParam('id'))),
        'method' => 'post',
    ));*/
    $form->setUseContainer(true);
		$this->setForm($form);
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('General')));

		$collection = Mage::getModel('bilna_formbuilder/form')->getCollection();
		//$collection->getSelect()->join(array('bff' => 'bilna_formbuilder_form'), 'main_table.id = bff.id', array('bff.title'));
		$collection->addFieldToSelect('title');
		$collection->addFieldToSelect('url');
		$collection->addFieldToSelect('active_from');
		$collection->addFieldToSelect('active_to');
		//$collection->addFieldToSelect(new Zend_Db_Expr("if(status = 0, 'Enabled', 'Disabled')"), 'status');
		$collection->addFieldToSelect('status');
		$collection->addFieldToFilter('main_table.id', (int) $this->getRequest()->getParam('id'));
		//$collection->addFieldToFilter('main_table.record_id', (int) $this->getRequest()->getParam('id'));
		$this->setCollection($collection);

		$data = $collection->getFirstItem();

		/*foreach($data->getData() as $key=>$value){
		echo $key."---".$value."<br>";
		}die;*/

		//$fieldset->addField('title', 'label', array(
		$fieldset->addField('title', 'text', array(
		   'label'	=> 'Title',
		   'name'   => 'title',
		   //'value'=> $data["title"], //dimatikan karena dibwh sdh ada $form->setValues($data);
    ));

		$fieldset->addField('url', 'text', array(
       'label'  => 'URL',
       'name'   => 'url',
       //'value'=> $data["url"],
    ));

		$outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

		$fieldset->addField('active_from', 'date', array(
       'label'  => $this->__('Aktif Dari'),
       'name'   => 'active_from',
			 'title'	=> $this->__('Aktif Dari'),
			 'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
			 'format'	=> $outputFormat,
       //'value'=> $data["active_from"],
    ));

		$fieldset->addField('active_to', 'date', array(
       'label'  => $this->__('Aktif Sampai'),
       'name'   => 'active_to',
			 'title'	=> $this->__('Aktif Sampai'),
			 'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
			 'format'	=> $outputFormat,
       //'value'=> $data["active_to"],
    ));

		$fieldset->addField('status', 'select', array(
       'label'			=> $this->__('Status'),
       //'class'   	=> 'required-entry',
       //'required'	=> true,
       'name'      	=> 'status',
       //'value'    => $data["status"],
       //'note'    	=> Mage::helper('bilna_formbuilder')->__('Status Note:</br>0=Enabled</br>1=Disabled'),
			 'values'     => array(
                     array(
                         'value' => 0,
                         'label' => Mage::helper('bilna_formbuilder')->__('Enabled'),
                     ),
                     array(
                         'value' => 1,
                         'label' => Mage::helper('bilna_formbuilder')->__('Disabled'),
                     ),
                 		 ),
       //'onchange'	=> 'checkStatusEnabled()'
    ));

		$form->setValues($data);
    return parent::_prepareForm();
	}
}
