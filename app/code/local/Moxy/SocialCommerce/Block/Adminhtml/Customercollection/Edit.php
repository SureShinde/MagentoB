<?php
	
class Moxy_SocialCommerce_Block_Adminhtml_Customercollection_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_removeButton("delete");
        $this->_objectId = "wishlist_id";
        $this->_blockGroup = "socialcommerce";
        $this->_controller = "adminhtml_customercollection";
        $this->_updateButton("save", "label", Mage::helper("socialcommerce")->__("Save Item"));

        $this->_addButton("saveandcontinue", array(
            "label"     => Mage::helper("socialcommerce")->__("Save And Continue Edit"),
            "onclick"   => "saveAndContinueEdit()",
            "class"     => "save",
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }";
    }

    public function getHeaderText()
    {
        return Mage::helper("socialcommerce")->__("Edit Item '%s'", $this->htmlEscape($this->getRequest()->getParam("id")));
    }
}

