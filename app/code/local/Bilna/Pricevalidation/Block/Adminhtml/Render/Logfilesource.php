<?php
    class Bilna_Pricevalidation_Block_Adminhtml_Render_Logfilesource extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
    {
        public function render(Varien_Object $row) {
            $baseDir = $row->getData('base_dir');
            if(!empty($baseDir)) {
                $baseDir .= '/';
            }
            if($row->getData('rows_errors') != 0) {
                $data = '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'files/pricevalidation/import/'.$baseDir.$row->getData('source_file').'">Download</a>';
            } else {
                $data = '-';
            }
            return $data;
        }
    }