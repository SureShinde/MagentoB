<?php
	
class Moxy_SocialCommerce_Block_Adminhtml_Collectioncategory_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "category_id";
				$this->_blockGroup = "socialcommerce";
				$this->_controller = "adminhtml_collectioncategory";
				$this->_updateButton("save", "label", Mage::helper("socialcommerce")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("socialcommerce")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("socialcommerce")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("collectioncategory_data") && Mage::registry("collectioncategory_data")->getId() ){

				    return Mage::helper("socialcommerce")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("collectioncategory_data")->getId()));

				} 
				else{

				     return Mage::helper("socialcommerce")->__("Add Item");

				}
		}
}