<?php

class Bilna_Staticarea_Block_Widget_Grid_Column_Renderer_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date {
    public function render(Varien_Object $row) {
        $data = $row->getData($this->getColumn()->getIndex());
        return @strtotime($data) < 1 ? '' : parent::render($row);
    }
}
