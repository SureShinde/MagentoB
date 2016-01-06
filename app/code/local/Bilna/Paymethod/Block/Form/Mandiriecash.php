<?php
/**
 * Description of Bilna_Paymethod_Block_Form_Mandiriecash
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Form_Mandiriecash extends Mage_Payment_Block_Form_Banktransfer {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('paymethod/form/mandiriecash.phtml');
    }
}
