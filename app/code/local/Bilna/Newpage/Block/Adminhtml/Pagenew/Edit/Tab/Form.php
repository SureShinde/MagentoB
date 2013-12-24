<?php
class Bilna_Newpage_Block_Adminhtml_Pagenew_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("newpage_form", array("legend"=>Mage::helper("newpage")->__("Item information")));

				
						$fieldset->addField("name", "text", array(
						"label" => Mage::helper("newpage")->__("name"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "name",
						));
					

				if (Mage::getSingleton("adminhtml/session")->getPagenewData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getPagenewData());
					Mage::getSingleton("adminhtml/session")->setPagenewData(null);
				} 
				elseif(Mage::registry("pagenew_data")) {
				    $form->setValues(Mage::registry("pagenew_data")->getData());
				}
				return parent::_prepareForm();
		}
}
