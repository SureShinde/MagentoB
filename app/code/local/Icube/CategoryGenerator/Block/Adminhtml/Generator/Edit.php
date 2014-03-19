<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Apply" button
     * Add "Save and Continue" button
     */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'categorygenerator';
        $this->_controller = 'adminhtml_generator';
        

        parent::__construct();

        $this->_addButton('save_apply', array(
            'class' => 'save',
            'label' => Mage::helper('categorygenerator')->__('Save and Apply'),
            'onclick' => "$('generator_auto_apply').value=1; editForm.submit()",
        ));

        $this->_addButton('save_and_continue_edit', array(
            'class' => 'save',
            'label' => Mage::helper('categorygenerator')->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
                ), 10);
    }

    /**
     * Getter for form header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $data = Mage::registry('cat_generator_data');
        if ($data->getId()) {
            return Mage::helper('categorygenerator')->__("Edit Generator '%s'", $this->escapeHtml($data->getName()));
        } else {
            return Mage::helper('categorygenerator')->__('New Generator');
        }
    }

}
