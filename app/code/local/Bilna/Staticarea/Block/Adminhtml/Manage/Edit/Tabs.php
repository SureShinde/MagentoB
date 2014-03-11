<?php

class Bilna_Staticarea_Block_Adminhtml_Manage_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('staticarea_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('staticarea')->__('Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('general', array(
            'label' => Mage::helper('staticarea')->__('General'),
			'title' => Mage::helper('staticarea')->__('General'),
			'content' => $this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_general')->toHtml()
        ));
        //Bilna_Staticarea_Block_Adminhtml_Manage_Edit_Tab_Contents
        $this->addTab('contents', array(
            'label' => $this->__('Contents'),
            'title' => $this->__('Contents'),
            'content' => $this->getLayout()->createBlock('staticarea/adminhtml_manage_edit_tab_contents')
                ->setData('bilnastaticarea_pid', $this->getRequest()->getParam('id'))
                ->toHtml()
        ));
        
        if($this->getRequest()->getParam('continue_tab'))
            $this->setActiveTab($this->getRequest()->getParam('continue_tab'));
        
        return parent::_beforeToHtml();
    }

}