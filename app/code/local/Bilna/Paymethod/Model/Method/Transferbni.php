<?php
/**
 * Description of Bilna_Paymethod_Model_Method_Transferbni
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Method_Transferbni extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'transferbni';
    
    protected $_formBlockType = 'payment/form_banktransfer';
    protected $_infoBlockType = 'payment/info_banktransfer';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions() {
        return trim($this->getConfigData('instructions'));
    }
}
