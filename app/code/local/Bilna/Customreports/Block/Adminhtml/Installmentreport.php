<?php  
class Bilna_Customreports_Block_Adminhtml_Installmentreport extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_blockGroup = 'customreports';
        $this->_controller = 'adminhtml_installmentreport';
        $this->_headerText = Mage::helper('customreports/installmentreport')->__('Installment Report');
        
        parent::__construct();
        
        $this->_removeButton('add');
    }
}
