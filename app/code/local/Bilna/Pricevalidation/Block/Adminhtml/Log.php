<?php
class Bilna_Pricevalidation_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'bilna_pricevalidation';
        $this->_controller = 'adminhtml_pricevalidation';
        $this->_headerText = $this->__('Price Validation Log');
        parent::__construct();
    }
}
