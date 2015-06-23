<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_addButton('apply_rules', array(
            'label' => $this->__('Apply Generator'),
            'onclick' => "location.href='" . $this->getUrl('*/*/applyGenerator') . "'",
            'class' => '',
        ));
        $this->_blockGroup = 'categorygenerator';
        $this->_controller = 'adminhtml_generator';
        $this->_headerText = $this->__('Category Generator');
        $this->_addButtonLabel = $this->__('Add New');
        parent::__construct();
    }

}
