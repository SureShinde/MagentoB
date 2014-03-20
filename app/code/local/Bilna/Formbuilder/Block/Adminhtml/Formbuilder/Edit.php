<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
      
{
   public function __construct()
   {
        parent::__construct();
        $this->_objectId = 'form_id';
        //we assign the same blockGroup as the Grid Container
        $this->_blockGroup = 'bilna_formbuilder';
        //and the same controller
        $this->_controller = 'adminhtml_formbuilder';
        //define the label for the save and delete button
        $this->_updateButton('save', 'label','save reference', $this->__('Save'));
        $this->_updateButton('delete', 'label', 'delete reference', $this->__('Delete'));
    }
       /* Here, we're looking if we have transmitted a form object,
          to update the good text in the header of the page (edit or add) */
    public function getHeaderText()
    {
        if(Mage::registry('bilna_formbuilder')->getId())
         {
              return $this->__('Edit Formbuilder');
         }
         else
         {
             return $this->__('New Formbuilder');
         }
    }
}
