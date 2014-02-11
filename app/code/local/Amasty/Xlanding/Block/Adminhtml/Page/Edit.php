<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Xlanding_Block_Adminhtml_Page_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amlanding';
        $this->_controller = 'adminhtml_page';
        
        $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('amlanding')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
        $this->_formScripts[] = "var landingConditionTmpl = '';";
        $this->_formScripts[] = "function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') }";
        $this->_formScripts[] = "function landingNewField(){ 
        	if ($('attributes')) { 
        		var tr = $('attributes').down('div').down('table').down('tbody').down('tr:last-child');
        		
        		/* Is New */ 
        		if (!tr) {
        			$('attributes').down('div').down('table').down('tbody').update(landingConditionTmpl);
				} else {
					tr.insert({'after': landingConditionTmpl});
				}
				
    		} 
    	}";
        $this->_formScripts[] = "function landingRemove(element) { 
        	if ($(element)) { 
        		$(element).up().up().next('tr').remove(); $(element).up().up().remove();
			} 
    	}
		";
        
        $this->_formScripts[] = "if ($('attributes')) { 
        	landingConditionTmpl = $('attributestmp').down('div').down('table').down('tbody').innerHTML; 
        	$('attributes').down('div').down('table').down('tbody').insert({after: '<tfoot><tr><td><button title=\"Save\" type=\"button\" class=\"scalable add\" onclick=\"landingNewField();return false;\"><span><span><span>Add New</span></span></span></button></td></tr></tfoot>'});}
        	$('attributestmp').previous('div').hide(); $('attributestmp').hide();
        ";
		$this->_formScripts[] = " function showOptions(sel) {
            new Ajax.Request('" . $this->getUrl('*/*/options', array('isAjax'=>true)) ."', {
                parameters: {code : sel.value},
                onSuccess: function(transport) {
                    sel.up('tr').next('tr').down('td').next('td').update(transport.responseText);
                }
            });
        }";         
    }

    public function getHeaderText()
    {
        $header = Mage::helper('amlanding')->__('New Landing Page');
        $model = Mage::registry('amlanding_page');
        if ($model->getId()){
            $header = Mage::helper('amlanding')->__('Edit Landing Page `%s`', $model->getTitle());
        }
        return $header;
    }
}