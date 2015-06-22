<?php
/**
 * Description of Bilna_Paymethod_Block_Form_Klikbca
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Form_Klikbca extends Mage_Payment_Block_Form {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('paymethod/form/klikbca.phtml');
    }
}
