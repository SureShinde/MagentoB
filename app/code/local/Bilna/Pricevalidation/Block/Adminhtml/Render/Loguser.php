<?php
    class Bilna_Pricevalidation_Block_Adminhtml_Render_Loguser extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
    {
        public function render(Varien_Object $row) {
            $model = Mage::getModel('admin/user')->load($row->getData('user_id'));
            $data = $model->getData('username');
            return $data;
        }
    }
