<?php
class Moxy_SocialCommerce_Block_Adminhtml_Collectioncategory_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("socialcommerce_form", array("legend"=>Mage::helper("socialcommerce")->__("Item information")));

        $fieldset->addField("name", "text", array(
            "label" => Mage::helper("socialcommerce")->__("Category name"),
            "name" => "name",
        ));

        $fieldset->addField("show_in_coll_page", "checkbox", array(
            "label" => Mage::helper("socialcommerce")->__("Show in collection page"),
            "onclick" => 'this.value = this.checked ? 1 : 0;',
            "name" => "show_in_coll_page",
        ));

        $fieldset->addField("is_active", "checkbox", array(
            "label" => Mage::helper("socialcommerce")->__("Is active"),
            "onclick" => 'this.value = this.checked ? 1 : 0;',
            "name" => "is_active",
        ));

        $fieldset->addField("sort_no", "text", array(
            "label" => Mage::helper("socialcommerce")->__("Sort no"),
            "name" => "sort_no",
        ));

        $fieldset->addField("url", "text", array(
            "label" => Mage::helper("socialcommerce")->__("Url"),
            "name" => "url",
        ));

        if (Mage::getSingleton("adminhtml/session")->getCollectioncategoryData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getCollectioncategoryData());
            Mage::getSingleton("adminhtml/session")->setCollectioncategoryData(null);
        } elseif (Mage::registry("collectioncategory_data")) {
            $form->setValues(Mage::registry("collectioncategory_data")->getData());
            $registry = Mage::registry("collectioncategory_data")->getData();
            $form->getElement("show_in_coll_page")->setIsChecked(!empty($registry["show_in_coll_page"]));
            $form->getElement("is_active")->setIsChecked(!empty($registry["is_active"]));
        }
        return parent::_prepareForm();
    }
}
