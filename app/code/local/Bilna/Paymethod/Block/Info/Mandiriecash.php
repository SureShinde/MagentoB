<?php
/**
 * Description of Bilna_Paymethod_Block_Info_Mandiriecash
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Info_Mandiriecash extends Mage_Payment_Block_Info_Banktransfer {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('paymethod/info/mandiriecash.phtml');
    }
}
