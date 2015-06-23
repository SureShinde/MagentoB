<?php
/**
 * Description of Index
 *
 * @path app/core/local/Bilna/Orderdetail/Block/Index.php
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Orderdetail_Block_Index extends Mage_Core_Block_Template {
    public function getFormActionUrl() {
        return sprintf("%sorderdetail/process/index", Mage::getBaseUrl());
    }
}
