<?php

class Bilna_Wrappinggiftevent_Block_Adminhtml_Manage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct() {
		parent::__construct();
		
		//$this->_removeButton('reset'); //remove reset button
		//$this->_removeButton('delete'); //remove delete button
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'wrappinggiftevent';
		$this->_controller = 'adminhtml_manage';
	
		$this->_updateButton('save', 'label', Mage::helper('wrappinggiftevent')->__('Save Wrap'));
		$this->_updateButton('delete', 'label', Mage::helper('wrappinggiftevent')->__('Delete Wrap'));
	
		$this->_addButton('saveandcontinue', array(
				'label' => Mage::helper('wrappinggiftevent')->__('Save And Continue Edit'),
				'onclick' => 'saveAndContinueEdit()',
				'class' => 'save',
		), -100);
	
		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('banner_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'banner_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'banner_content');
                }
            }
	
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
	
            function showTypeContents(){
                var typeId=$('banner_type').value;
                var show = ((typeId==0)?'block':'none');
                var hide = ((typeId==0)?'none':'block');
                $('filename').setStyle({display:show});
                $('filename_delete').setStyle({display:show});
                $('banner_content').setStyle({display:hide});
                setTimeout('bannerContentType()',1000);
                alert($('filename').getStyle('display'))
            }
     
            function bannerContentType(){
                var typeId=$('banner_type').value;
                var hide = ((typeId==0)?'none':'block');
                $('buttonsbanner_content').setStyle({display:hide});
                $('banner_content_parent').setStyle({display:hide});
            }
	
	
            /* Event.observe('banner_type', 'change', function(){
                    showTypeContents();
                });
            Event.observe(window, 'load', function(){
                    showTypeContents();
                }); */
        ";
	}
	
	public function getHeaderText() {
		if (Mage::registry('wrappinggiftevent_data') && Mage::registry('wrappinggiftevent_data')->getId()) {
			return Mage::helper("wrappinggiftevent")->__("Edit Wrap '%s'", $this->htmlEscape(Mage::registry("wrappinggiftevent_data")->getpromo_title()));
		} else {
			return Mage::helper('wrappinggiftevent')->__('Add Wrap');
		}
	}
	
}
