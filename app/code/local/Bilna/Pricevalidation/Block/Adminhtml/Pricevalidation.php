<?php
class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'bilna_pricevalidation';
        $this->_controller = 'adminhtml_pricevalidation';
        $this->_headerText = $this->__('Price Validation');
        $this->_addButtonLabel = Mage::helper('bilna_pricevalidation')->__('Add Profile');
        parent::__construct();
    }
}
