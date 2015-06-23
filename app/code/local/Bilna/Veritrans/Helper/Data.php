<?php
class Bilna_Veritrans_Helper_Data extends Mage_Core_Helper_Abstract {
    public $_maxAddressChar = 30;
    public $_maxCommodityChar = 26;
    
    public function getInstallmentOption() {
        return '';
    }
    
    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }
    
    public function getMerchantId() {
        return Mage::getStoreConfig('payment/veritrans/merchant_id');
    }
    
    public function getMerchantHashKey() {
        return Mage::getStoreConfig('payment/veritrans/merchant_hash_key');
    }
    
    public function getOrderId() {
        return $this->getCheckout()->getLastOrderId();
    }
    
    public function getSessionId() {
        return Mage::getSingleton('core/session')->getEncryptedSessionId();
    }
    
    public function splitAddress($addressObj) {
        $_address1 = $this->removeSymbols($addressObj->getStreet(1));
        $_address2 = $this->removeSymbols($addressObj->getStreet(2));

        if (strlen($_address1) > $this->_maxAddressChar) {
            $address = $_address1 . ' ' . $_address2;

            if (strlen($address) > $this->_maxAddressChar) {
                $address1 = substr($address, 0, $this->_maxAddressChar);
                $address2 = substr($address, $this->_maxAddressChar, $this->_maxAddressChar);
    	    }
            else {
                $address1 = $address;
                $address2 = '';
            }
        }
        else {
            $address1 = substr($_address1, 0, $this->_maxAddressChar);
            $address2 = substr($_address2, 0, $this->_maxAddressChar);
        }

        return array (
            'address1' => trim($address1),
            'address2' => trim($address2)
        );
    }
    
    public function getCountryCode($countryId) {
        if ($countryId == 'ID') {
            return 'IDN';
        }

        return '';
    }
    
    public function useBillingAddressForShippingAddress() {
        //soon
        return false;
    }
    
    public function getCommodityInformation($orderData) {
        $result = array (
            array (
                'COMMODITY_ID' => 1,
                'COMMODITY_UNIT' => (int) $orderData['grand_total'],
                'COMMODITY_NUM' => 1,
                'COMMODITY_NAME1' => $this->removeSymbols('Total Item'),
                'COMMODITY_NAME2' => $this->removeSymbols('Total Item')
            )
        );

       	return $result;
    }
    
    public function removeSymbols($text) {
        $disallowedSymbol = Mage::getStoreConfig('payment/veritrans/disallowed_symbol');
        $disallowedSymbolArr = explode(' ', $disallowedSymbol);

        return str_replace($disallowedSymbolArr, ' ', $text);
    }
    
    public function getFinishUrl() {
        return sprintf("%s%s", Mage::getBaseUrl(), Mage::getStoreConfig('payment/veritrans/url_success'));
    }
    
    public function getUnfinishUrl() {
        return sprintf("%s%s", Mage::getBaseUrl(), Mage::getStoreConfig('payment/veritrans/url_cancel'));
    }
    
    public function getFailureUrl() {
        return sprintf("%scheckout/onepage/failure/", Mage::getBaseUrl());
    }
    
    public function getRedirectUrl() {
        return Mage::getStoreConfig('payment/veritrans/url_redirection');
    }
    
    public function getRedirectTimeout() {
        return (int) Mage::getStoreConfig('payment/veritrans/redirect_timeout') * 1000;
    }
    
    public function getTrxLogPath() {
        return Mage::getStoreConfig('payment/veritrans/trx_log_path');
    }
    
    public function checkLockFile($filename) {
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('payment/veritrans/base_log_path'));
        $notifLockPath = Mage::getStoreConfig('payment/veritrans/notif_lock_path');
        $fullFilename = sprintf("%s%s%s.lock", $baseLogPath, $notifLockPath, $filename);
        
        if (file_exists($fullFilename)) {
            return false;
        }
        
        return $this->writeLockFile($filename);
    }
    
    public function writeLockFile($filename) {
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('payment/veritrans/base_log_path'));
        $notifLockPath = Mage::getStoreConfig('payment/veritrans/notif_lock_path');
        
        // create base log path folder if not exit
        if (!file_exists($baseLogPath)) {
            mkdir($baseLogPath, 0777, true);
        }
        
        // create path folder if not exit
        if (!file_exists($baseLogPath . $notifLockPath)) {
            mkdir($baseLogPath . $notifLockPath, 0777, true);
        }
        
        $fullFilename = sprintf("%s%s%s.lock", $baseLogPath, $notifLockPath, $filename);
        $handle = fopen($fullFilename, 'w');
        fwrite($handle, $content . "\n");
        fclose($handle);

        return true;
    }
    
    public function removeLockFile($filename) {
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('payment/veritrans/base_log_path'));
        $notifLockPath = Mage::getStoreConfig('payment/veritrans/notif_lock_path');
        $fullFilename = sprintf("%s%s%s.lock", $baseLogPath, $notifLockPath, $filename);
        
        if (file_exists($fullFilename)) {
            if (unlink($fullFilename)) {
                return true;
            }
            
            return false;
        }
        
        return false;
    }
    
    public function writeLogFile($path, $filename, $content, $type = 'debug') {
        if ($type == 'debug') {
            $currDateMagento = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
            $content = sprintf("%s DEBUG: %s", $currDateMagento, $content);
        }

        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('payment/veritrans/base_log_path'));

        // create base log path folder if not exit
        if (!file_exists($baseLogPath)) {
            mkdir($baseLogPath, 0777, true);
        }

        // create path folder if not exit
        if (!file_exists($baseLogPath . $path)) {
            mkdir($baseLogPath . $path, 0777, true);
        }

        $fullFilename = sprintf("%s%s%s.log", $baseLogPath, $path, $filename);

        if (file_exists($fullFilename)) {
            $handle = fopen($fullFilename, 'a');
        }
        else {
            $handle = fopen($fullFilename, 'w');
        }

        fwrite($handle, $content . "\n");
        fclose($handle);

        return true;
    }
}
