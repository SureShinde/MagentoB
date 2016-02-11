<?php

class Bilna_Wrappinggiftevent_Block_Adminhtml_Manage_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('wrappinggiftevent_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('wrappinggiftevent')->__('Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('wrappinggiftevent')->__('Wrap Type'),
			'title' => Mage::helper('wrappinggiftevent')->__('Wrap Type'),
			'content' => $this->getLayout()->createBlock('wrappinggiftevent/adminhtml_manage_edit_tab_form')->toHtml(),
            'active' => true
        ));
        
        return parent::_beforeToHtml();
    }

}