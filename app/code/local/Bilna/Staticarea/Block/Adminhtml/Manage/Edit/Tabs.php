<?php

class Bilna_Staticarea_Block_Adminhtml_Manage_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('staticarea_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('staticarea')->__('Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('staticarea')->__('General'),
			'title' => Mage::helper('staticarea')->__('General'),
			'content' => $this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_general')->toHtml(),
            'active' => true
        ));

        $this->addTab('contents', array(
            'label' => $this->__('Contents'),
            'title' => $this->__('Contents'),
            'content' => $this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_contents')->toHtml()
        ));
        
        return parent::_beforeToHtml();
    }

}