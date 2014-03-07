<?php
class Bilna_Customreports_Block_Adminhtml_Renderer_Column_Installment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $method = strtolower($row->getData('method'));
        $paymentType = $row->getData($this->getColumn()->getIndex());
        
        $installmentCollection = $this->helper($method)->getInstallmentOptionCollection();
        $installmentOption = array ();
       
        foreach ($installmentCollection as $key => $value) {
            if ($value['id'] == $paymentType) {
                return $value['label'];
            }
        }
        
        return '-';
    }
}
