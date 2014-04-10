<?php
/**
 * Description of Bilna_Netsuite_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Netsuite_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getEmail() {
        return Mage::getStoreConfig('bilna_netsuite/netsuite/email');
    }
    
    public function getPassword() {
        return Mage::getStoreConfig('bilna_netsuite/netsuite/password');
    }
    
    public function getAccount() {
        return Mage::getStoreConfig('bilna_netsuite/netsuite/account');
    }
    
    public function getRole() {
        return Mage::getStoreConfig('bilna_netsuite/netsuite/role');
    }
}
