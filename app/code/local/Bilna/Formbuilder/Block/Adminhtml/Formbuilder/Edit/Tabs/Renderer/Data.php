<?php
/**
 * Description of Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Renderer_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Renderer_Data extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $value = $row->getData($this->getColumn()->getIndex());
        $result = Mage::helper('bilna_formbuilder')->decrypt($value);
        
        return $result;
    }
}
