<?php
/**
 * Description of Index
 *
 * @path app/core/local/Bilna/Paymentconfirmation/Block/Index.php
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymentconfirmation_Block_Index extends Mage_Core_Block_Template {
    public function getFormActionUrl() {
        return sprintf("%skonfirmasipembayaran/index/process", Mage::getBaseUrl());
    }
    public function getFormValidateUrl() {
        return sprintf("%skonfirmasipembayaran/index/validateOrder", Mage::getBaseUrl());
    }
}

