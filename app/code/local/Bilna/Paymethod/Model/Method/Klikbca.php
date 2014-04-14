<?php
/**
 * Description of Klikbca
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Method_Klikbca extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'klikbca';
    
    protected $_formBlockType = 'paymethod/form_klikbca';
    protected $_infoBlockType = 'paymethod/info_klikbca';
    
    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        
        $info = $this->getInfoInstance();
        $info->setKlikbcaUserId($data->getKlikbcaUserId());
        
        return $this;
    }
    
    public function validate() {
        parent::validate();
        
        $info = $this->getInfoInstance();
        $no = $info->getKlikbcaUserId();
        
        if (empty ($no)) {
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('KlikBCA User id is required field.');
        }
        
        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }
        
        return $this;
    }
}
