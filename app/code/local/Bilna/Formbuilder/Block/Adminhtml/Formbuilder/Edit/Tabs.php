<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
  {
   public function __construct()
   {
        parent::__construct();
        $this->setId('formbuilder_form_tabs');
        $this->setDestElementId('edit_form');
				$this->setTitle(Mage::helper('bilna_formbuilder')->__('Form Information'));
    }

    protected function _beforeToHtml()
    {
        /*$this->addTab('form_section', array(
					//'label' => 'Form Information',
					'label' => Mage::helper('bilna_formbuilder')->__('Form Information'),									
					//'title' => 'Form Information',
					'alt' => Mage::helper('bilna_formbuilder')->__('Form Information'),
					//'content' => $this->getLayout()->createBlock('formbuilder/adminhtml_formbuilder_edit_tab_form')->toHtml(),
					'content' => $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Form')->toHtml(),));
       return parent::_beforeToHtml();*/

        $this->addTab('form_section', array(
            //'label' => Mage::helper('bilna_formbuilder')->__('Form Section'),
            //'title' => Mage::helper('bilna_formbuilder')->__('Form Section'),
            //'content' => $this->getLayout()->createBlock('formbuilder/adminhtml_formbuilder_edit_tabs_form')->toHtml(),
						'content' => $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Form')->toHtml(),
            //'active' => Mage::registry('formbuilder_formbuilder')->getId() ? false : true
        ));
				return parent::_beforeToHtml();
		}

}
