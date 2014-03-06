<?php
class Phpro_Stockmonitor_Block_Adminhtml_General_Overview extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_blockGroup = 'stockmonitor';
        $this->_controller = 'adminhtml_general_overview';
        $this->_headerText = Mage::helper('catalog')->__('General Product Order Overview');
        
        parent::__construct();
        
        $this->_removeButton('add');
    }
}
