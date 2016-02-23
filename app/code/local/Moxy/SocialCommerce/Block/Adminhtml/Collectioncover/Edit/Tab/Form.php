<?php
class Moxy_SocialCommerce_Block_Adminhtml_Collectioncover_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("socialcommerce_form", array("legend"=>Mage::helper("socialcommerce")->__("Item information")));

								
						$fieldset->addField('image', 'image', array(
						'label' => Mage::helper('socialcommerce')->__('image file'),
						'name' => 'image',
						'note' => '(*.jpg, *.png, *.gif)',
						));
						$fieldset->addField("caption", "text", array(
						"label" => Mage::helper("socialcommerce")->__("image caption"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "caption",
						));
									
						 $fieldset->addField('category_id', 'select', array(
						'label'     => Mage::helper('socialcommerce')->__('category'),
						'values'   => Moxy_SocialCommerce_Block_Adminhtml_Collectioncover_Grid::getValueArray5(),
						'name' => 'category_id',					
						"class" => "required-entry",
						"required" => true,
						));

				if (Mage::getSingleton("adminhtml/session")->getCollectioncoverData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getCollectioncoverData());
					Mage::getSingleton("adminhtml/session")->setCollectioncoverData(null);
				} 
				elseif(Mage::registry("collectioncover_data")) {
				    $form->setValues(Mage::registry("collectioncover_data")->getData());
				}
				return parent::_prepareForm();
		}
}
