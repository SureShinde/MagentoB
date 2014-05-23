<?php


class Bilna_Staticarea_Block_Adminhtml_Manage_Edit_Tab_Contents_Container extends Mage_Adminhtml_Block_Widget_Container {
    protected function _beforeToHtml() {
        if($this->getData('image_id'))
            $this->_headerText = $this->__('Edit Content');
        else
            $this->_headerText = $this->__('Add Content');
        $this->setTemplate('bilna_staticarea/contents/form_container.phtml');
    }

    public function getFormKeyHtml() {
        $_formKeyBlock = $this->getLayout()->getBlock('formkey');
        if(!$_formKeyBlock)
            $_formKeyBlock = $this->getLayout()->createBlock('core/template', 'formkey')->setTemplate('formkey.phtml');
        return $_formKeyBlock ? $_formKeyBlock->toHtml() : null;
    }

    protected function _prepareLayout() {
        $this->_addButton('save', array(
            'label'   => $this->__('Save'),
            'type' => 'submit',
            'class'   => 'save',
            'id' => 'bilna_contentsavebutton'
        ));

        $this->setChild('form', $this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_contents_ajaxform_form'));
        
        return parent::_prepareLayout();
    }
}
