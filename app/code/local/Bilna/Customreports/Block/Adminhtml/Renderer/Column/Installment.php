<?php
class Bilna_Customreports_Block_Adminhtml_Renderer_Column_Installment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $paymentType = $row->getData('installment');
        $tenor = $row->getData('installment_type');
        
        if ($paymentType == 0) {
            $tenor = '';
        }
        
        return $tenor;
    }
}
