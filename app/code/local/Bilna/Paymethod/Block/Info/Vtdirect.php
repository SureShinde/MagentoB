<?php
/**
 * Description of Bilna_Paymethod_Block_Info_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Info_Vtdirect extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('paymethod/info/vtdirect.phtml');
    }
}
