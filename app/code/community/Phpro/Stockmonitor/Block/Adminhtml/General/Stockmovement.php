<?php
class Phpro_Stockmonitor_Block_Adminhtml_General_Stockmovement extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_general_stockmovement';
    $this->_blockGroup = 'stockmonitor';
    $this->_headerText = Mage::helper('catalog')->__('Stockmovement');
    parent::__construct();
    $this->_removeButton('add');
  }
}