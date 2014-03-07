<?php


class Bilna_Staticarea_Block_Adminhtml_Area_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);
        $_data = Mage::helper('staticarea')->getFormData($this->getRequest()->getParam('id'));
        
    }
}
