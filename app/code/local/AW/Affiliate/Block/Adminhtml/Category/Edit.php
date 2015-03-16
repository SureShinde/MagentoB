<?php

class AW_Affiliate_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_category';
        $this->_objectId = 'id';
        $this->_blockGroup = 'awaffiliate';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save Category'));
        $this->_updateButton('delete', 'label', $this->__('Delete Category'));
    }

    public function getHeaderText()
    {
        return $this->__('New Category');
    }

    protected function _prepareLayout()
    {
        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('customer')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
            'class' => 'save'
        ), 10);
        $this->_formScripts[] = "function saveAndContinueEdit(url){"
            . "    var tabId = awaffiliate_category_tabsJsTabs.activeTab.getAttribute('name');"
            . "    url = url.replace(/{{tab_id}}/, tabId);"
            . "    editForm.submit(url)"
            . " }";
        return parent::_prepareLayout();
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current' => true,
            'back' => 'edit',
            'tab' => '{{tab_id}}'
        ));
    }
}
