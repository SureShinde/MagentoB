<?php
    class Bilna_Pricevalidation_Block_Adminhtml_Render_Logfile extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
    {
        public function render(Varien_Object $row) {
            $baseDir = '';
            $model  = Mage::getModel('bilna_pricevalidation/profile')->load($row->getData('profile_id'));
            if(!empty($model->getBaseDir())) {
                $baseDir = $model->getBaseDir().'/';
            }
            if($row->getData('rows_errors') != 0) {
                $data = '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'var/pricevalidation/import/'.$baseDir.$row->getData('error_file').'">Download</a>';
            } else {
                $data = '-';
            }
            return $data;
        }
    }