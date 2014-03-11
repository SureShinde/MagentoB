<?php

class Bilna_Staticarea_Block_Adminhtml_Manage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct() {
		parent::__construct();
		
		//$this->_removeButton('reset'); //remove reset button
		//$this->_removeButton('delete'); //remove delete button
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'staticarea';
		$this->_controller = 'adminhtml_manage';
	
		$this->_updateButton('save', 'label', Mage::helper('staticarea')->__('Save Static Area'));
		$this->_updateButton('delete', 'label', Mage::helper('staticarea')->__('Delete Static Area'));
	
		if($this->getRequest()->getParam('id')) {
            $this->_addButton('addcontent', array(
                'label' => $this->__('Add Content'),
                'onclick' => 'bilnaisAddContent()',
                'class' => 'add',
                'id' => 'bilna-add-content'
            ), 0);
        }

		$this->_addButton('saveandcontinue', array(
				'label' => Mage::helper('staticarea')->__('Save And Continue Edit'),
				'onclick' => 'saveAndContinueEdit()',
				'class' => 'save',
		), -100);
	
		$this->_formScripts[] = "
        function bilnaisAddContent() {
            staticarea_tabsJsTabs.tabs[1].show();
            bilnaISAjaxForm.showForm(".$this->getRequest()->getParam('id').");
        }
        function awis_prepareForm() {
        }
        function awisSaveAndContinueEdit() {
            if($('edit_form').action.indexOf('continue/1/')<0)
                $('edit_form').action += 'continue/1/';
            if($('edit_form').action.indexOf('continue_tab/')<0)
                $('edit_form').action += 'continue_tab/'+staticarea_tabsJsTabs.activeTab.name+'/';
            awis_prepareForm();
            editForm.submit();
        }
        if(bilnaISSettings)
            bilnaISSettings.setOption('imagesAjaxFormUrl', '{$this->getUrl('staticarea/adminhtml_manage/ajaxform')}');
	
        function saveAndContinueEdit(){
            editForm.submit($('edit_form').action+'back/edit/');
        }
        ";
	}
	
	public function getHeaderText() {
		if (Mage::registry('staticarea_data') && Mage::registry('staticarea_data')->getId()) {
			return Mage::helper("staticarea")->__("Edit Static Area '%s'", $this->htmlEscape(Mage::registry("staticarea_data")->getpromo_title()));
		} else {
			return Mage::helper('staticarea')->__('Add Static Area');
		}
	}
	
}
