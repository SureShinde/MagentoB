<?php

class Bilna_Staticarea_Block_Adminhtml_Area_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('staticarea_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Area Information'));
    }
    
    protected function _beforeToHtml() {
        $this->addTab('general', array(
            'label' => $this->__('General'),
            'title' => $this->__('General'),
            'content' => $this->getLayout()->createBlock('staticarea/adminhtml_area_edit_tabs_general')->toHtml()
        ));

        $this->addTab('images', array(
            'label' => $this->__('Images'),
            'title' => $this->__('Images')
        ));

        return parent::_beforeToHtml();
    }
}
