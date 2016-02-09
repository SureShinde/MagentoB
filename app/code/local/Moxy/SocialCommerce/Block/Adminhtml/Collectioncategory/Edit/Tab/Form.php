<?php
class Moxy_SocialCommerce_Block_Adminhtml_Collectioncategory_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("socialcommerce_form", array("legend"=>Mage::helper("socialcommerce")->__("Item information")));

				
						$fieldset->addField("name", "text", array(
						"label" => Mage::helper("socialcommerce")->__("name"),
						"name" => "name",
						));
					

				if (Mage::getSingleton("adminhtml/session")->getCollectioncategoryData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getCollectioncategoryData());
					Mage::getSingleton("adminhtml/session")->setCollectioncategoryData(null);
				} 
				elseif(Mage::registry("collectioncategory_data")) {
				    $form->setValues(Mage::registry("collectioncategory_data")->getData());
				}
				return parent::_prepareForm();
		}
}
