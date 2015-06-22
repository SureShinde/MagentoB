<?php
/**
 * Description of Bilna_Bnicc_Model_Bnicc
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Bnicc_Model_Bnicc extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'bnicc';
    protected $_formBlockType = 'bnicc/form_bnicc';
    protected $_infoBlockType = 'bnicc/info_bnicc';
    
    protected $_canAuthorize = true;
    
    const PAYMENT_TYPE_FULL_TRANSACTION = '01';
    const PAYMENT_TYPE_INSTALLMENT_TRANSACTION = '02';
    const PAYMENT_TYPE_COMBINE_TRANSACTION = '03';
    
    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        
        $info = $this->getInfoInstance();
        $info->setCcBins($data->getCcBins());
        
        return $this;
    }
    
    public function validate() {
        parent::validate();
        
        $info = $this->getInfoInstance();
        $no = $info->getCcBins();
        
        if (empty ($no)) {
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('BNI Bin Number is required field.');
        }
        
        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }
        
        return $this;
    }

    public function authorize(Varien_Object $payment, $amount) {
        return true;
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl($this->_code . '/processing', array ('_secure' => true));
    }
    
    public function getCCBinsByEntityId($entityId) {
        $dbRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $sql = "SELECT cc_bins ";
        $sql .= "FROM sales_flat_order_payment ";
        $sql .= sprintf("WHERE entity_id = %d ", $entityId);
        $sql .= "LIMIT 1 ";
        
        $query = $dbRead->query($sql);
        $rows = $query->fetch();
        
        return $rows['cc_bins'];
    }
}
