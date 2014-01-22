<?php  
class Bilna_Anzcc_Block_Adminhtml_Anzccbackend extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_blockGroup = 'anzcc';
        $this->_controller = 'adminhtml_anzccbackend';
        $this->_headerText = Mage::helper('anzcc')->__('Installment Report');
        
        parent::__construct();
        
        $this->_removeButton('add');
    }
}
