<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */   
class Amasty_Alert_Block_Adminhtml_Price extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_price';
        $this->_blockGroup = 'amalert';
        $this->_headerText = Mage::helper('amalert')->__('Price Alerts');
        $this->_removeButton('add'); 
    }
}