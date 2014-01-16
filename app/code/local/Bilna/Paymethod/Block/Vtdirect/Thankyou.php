<?php
/**
 * Description of Bilna_Paymethod_Block_Vtdirect_Thankyou
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Vtdirect_Thankyou extends Mage_Core_Block_Template {
    public function getResponseCharge() {
        return Mage::registry('response_charge');
    }
}
