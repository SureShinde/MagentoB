<?php

class Bilna_Staticarea_Block_Adminhtml_Area_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct()
    {
        //$this->_removeButton('reset'); //remove reset button
        //$this->_removeButton('delete'); //remove delete button
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'staticarea';
        $this->_controller = 'adminhtml_area';
    
        $this->_updateButton('save', 'label', Mage::helper('staticarea')->__('Save Area'));
        $this->_updateButton('delete', 'label', Mage::helper('staticarea')->__('Delete Area'));
    
        $this->_addButton('saveandcontinue', array(
                'label' => Mage::helper('staticarea')->__('Save And Continue Edit'),
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
    
    public function getHeaderText()
    {
        return $this->__('Slider');
    }
}
