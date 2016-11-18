<?php
class Bilna_Smsverification_IndexController extends Mage_Core_Controller_Front_Action {
    public function IndexAction() {
        Mage::log("Deni Wavecell Param: ".json_encode($_REQUEST));
    }

}
