<?php

class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'profile_id';
        $this->_blockGroup = 'bilna_pricevalidation';
        $this->_controller = 'adminhtml_pricevalidation';

        $hlp = Mage::helper('bilna_pricevalidation');
        $profile = Mage::registry('profile_data');
        $id = $profile->getId();

        if ($id) {
            $this->_addButton('start_bg', array(
                'label'     => $this->__('Save And Run'),
                'onclick'   => "editForm.submit(\$('edit_form').action+'start/ondemand/back/edit/profile_id/".$id."')",
                'class'     => 'save',
            ), 0);
        }
        else {
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                'onclick'   => "editForm.submit(\$('edit_form').action+'back/edit/')",
                'class'     => 'save',
            ), -100);
        }
    }

    public function getHeaderText()
    {
        $profile = Mage::registry('profile_data');
        $hlp = Mage::helper('bilna_pricevalidation');

        if ($profile && $profile->getId()) {
            $title = $this->htmlEscape($profile->getTitle());
            $title = $this->__("Edit Profile '%s'", $title);
            return $title;
        } else {
            return $this->__('Add Profile');
        }
    }
}
