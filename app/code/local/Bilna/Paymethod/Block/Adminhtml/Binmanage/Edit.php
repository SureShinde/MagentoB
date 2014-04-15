<?php
/**
 * Description of Bilna_Paymethod_Block_Adminhtml_Binmanage_Edit
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Adminhtml_Binmanage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        parent::__construct();
 
        $this->_objectId = 'id';
        $this->_blockGroup = 'paymethod';
        $this->_controller = 'adminhtml_binmanage';
        $this->_mode = 'edit';
 
        $this->_addButton('save_and_continue', array (
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);
        $this->_updateButton('save', 'label', Mage::helper('paymethod/binmanage')->__('Save'));
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText() {
        $helper = Mage::helper('paymethod/binmanage');
        
        if (!is_null(Mage::registry('binmanage_data')->getId())) {
            return $helper->__('Edit Bin Number "%s"', $this->escapeHtml(Mage::registry('binmanage_data')->getCode()));
        }
        else {
            return $helper->__('New Bin Information');
        }
    }
    
    protected function _prepareLayout() {
        if ($this->_blockGroup && $this->_controller && $this->_mode) {
            $this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_form'));
        }
        
        return parent::_prepareLayout();
    }
}
