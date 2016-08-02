<?php
class Moxy_SocialCommerce_Block_Adminhtml_Customercollection_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $categories = Mage::getSingleton("socialcommerce/source");
        $this->setForm($form);
        $fieldset = $form->addFieldset("socialcommerce_form", array("legend"=>Mage::helper("socialcommerce")->__("Item information")));

        $fieldset->addField("name", "text", array(
            "label" => Mage::helper("socialcommerce")->__("Collection name"),
            "name" => "name",
            "readonly" => true
        ));

        $fieldset->addField("collection_category_id", "multiselect", array(
            "label" => Mage::helper("socialcommerce")->__("Collection categories"),
            "name" => "categories",
            "values" => $categories->toOptionArray()
        ));

        if (Mage::getSingleton("adminhtml/session")->getCustomercollectionData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getCustomercollectionData());
            Mage::getSingleton("adminhtml/session")->setCustomercollectionData(null);
        } elseif (Mage::registry("customercollection_data")) {
            $form->setValues(Mage::registry("customercollection_data"));
        }

        return parent::_prepareForm();
    }
}
