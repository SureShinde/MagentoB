<?php
/**
 * Description of Bilna_Paymethod_Model_Method_Virtualaccountbca
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Method_Virtualaccountbca extends Mage_Payment_Model_Method_Banktransfer {
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'virtualaccountbca';

//    protected $_formBlockType = 'payment/form_banktransfer';
    protected $_infoBlockType = 'payment/info_virtualaccountbca';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions() {
        return trim($this->getConfigData('instructions'));
    }
}
