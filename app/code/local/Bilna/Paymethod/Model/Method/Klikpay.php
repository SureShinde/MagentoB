<?php
/**
 * Description of Bilna_Paymethod_Model_Method_Klikpay
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Method_Klikpay extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'klikpay';
    protected $_formBlockType = 'paymethod/form_klikpay';
    protected $_infoBlockType = 'paymethod/info_klikpay';
    
    const PAYMENT_TYPE_FULL_TRANSACTION = '01';
    const PAYMENT_TYPE_INSTALLMENT_TRANSACTION = '02';
    const PAYMENT_TYPE_COMBINE_TRANSACTION = '03';
    
    public function assignData($data) {
        
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        
        $info = $this->getInfoInstance();
        
        return $this;
    }

    public function validate() {
        parent::validate();
        
        $info = $this->getInfoInstance();
		
        return $this;
    }
    
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('paymethod/klikpay/redirect', array ('_secure' => true));
    }
}
