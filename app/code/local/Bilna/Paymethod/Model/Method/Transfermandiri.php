<?php
/**
 * Description of Bilna_Paymethod_Model_Method_Transfermandiri
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Method_Transfermandiri extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'transfermandiri';
    
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
