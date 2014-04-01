<?php
/**
 * Description of Bilna_Paymethod_Block_Adminhtml_Binmanage
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Adminhtml_Binmanage extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_blockGroup = 'paymethod';
        $this->_controller = 'adminhtml_binmanage';
        $this->_headerText = Mage::helper('paymethod')->__('Bin Management');
        $this->_addButtonLabel = Mage::helper('paymethod')->__('Add New Bin Information');
        
        parent::__construct();
    }
}
