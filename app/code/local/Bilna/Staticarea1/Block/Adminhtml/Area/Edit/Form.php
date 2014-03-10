<?php


class Bilna_Staticarea_Block_Adminhtml_Area_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $_form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'
        ));

        $_form->setUseContainer(TRUE);
        $this->setForm($_form);
        return parent::_prepareForm();
    }
}
