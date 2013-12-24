<?php
	
class Bilna_Newpage_Block_Adminhtml_Pagenew_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "newpage";
				$this->_controller = "adminhtml_pagenew";
				$this->_updateButton("save", "label", Mage::helper("newpage")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("newpage")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("newpage")->__("Save And Continue Edit"),
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
				if( Mage::registry("pagenew_data") && Mage::registry("pagenew_data")->getId() ){

				    return Mage::helper("newpage")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("pagenew_data")->getId()));

				} 
				else{

				     return Mage::helper("newpage")->__("Add Item");

				}
		}
}