<?php
/**
 * Description of Bilna_Paymethod_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getPaymentMethodBankTransfer() {
        $payment = Mage::getStoreConfig('bilna_module/success_page/payment_transfer');
        $result = explode(',', $payment);
        
        return $result;
    }
    
    public function getPaymentMethodKlikpay() {
        $payment = Mage::getStoreConfig('bilna_module/success_page/payment_klikpay');
        $result = explode(',', $payment);
        
        return $result;
    }
    
    public function getPaymentMethodCc() {
        $payment = Mage::getStoreConfig('bilna_module/success_page/payment_cc');
        $result = explode(',', $payment);
        
        return $result;
    }
    
    public function getInstallmentOption($paymentMethod, $id, $returnKey = 'label') {
        $installmentOptions = unserialize(Mage::getStoreConfig('payment/' . $paymentMethod . '/installment'));
        
        foreach ($installmentOptions as $_option) {
            if ($_option['id'] == $id) {
                return $_option[$returnKey];
            }
        }
        
        return;
    }
    
    public function writeLogFile($module, $type, $filename, $content, $logType = 'debug') {
        if ($logType == 'debug') {
            $currDateMagento = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
            $content = sprintf("%s DEBUG: %s", $currDateMagento, $content);
        }
        
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('bilna_module/paymethod/log_path'));
        $moduleLogPath = sprintf("%s%s/", $baseLogPath, $module);
        $typeLogPath = sprintf("%s%s/", $moduleLogPath, $type);
        
        // create base log path folder if not exit
        if (!file_exists($baseLogPath)) {
            mkdir($baseLogPath, 0777, true);
        }
        
        // create module path folder if not exit
        if (!file_exists($moduleLogPath)) {
            mkdir($moduleLogPath, 0777, true);
        }
        
        // create type path folder if not exit
        if (!file_exists($typeLogPath)) {
            mkdir($typeLogPath, 0777, true);
        }
        
        $fullFilename = sprintf("%s%s.log", $typeLogPath, $filename);
        
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
    
    public function moveFile($oldFilename, $newFilename, $module, $type) {
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('bilna_module/paymethod/log_path'));
        $moduleLogPath = sprintf("%s%s/", $baseLogPath, $module);
        $typeLogPath = sprintf("%s%s/", $moduleLogPath, $type);
        
        // create base log path folder if not exit
        if (!file_exists($baseLogPath)) {
            mkdir($baseLogPath, 0777, true);
        }
        
        // create module path folder if not exit
        if (!file_exists($moduleLogPath)) {
            mkdir($moduleLogPath, 0777, true);
        }
        
        // create type path folder if not exit
        if (!file_exists($typeLogPath)) {
            mkdir($typeLogPath, 0777, true);
        }
        
        $filename = $typeLogPath . $newFilename;
        
        if (rename($oldFilename, $filename)) {
            return true;
        }
        
        return false;
    }
    
    public function createLockFile($module, $filename) {
        $type = 'locks';
        $content = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('bilna_module/paymethod/log_path'));
        $moduleLogPath = sprintf("%s%s/", $baseLogPath, $module);
        $typeLogPath = sprintf("%s%s/", $moduleLogPath, $type);
        
        // create base log path folder if not exit
        if (!file_exists($baseLogPath)) {
            mkdir($baseLogPath, 0777, true);
        }
        
        // create module path folder if not exit
        if (!file_exists($moduleLogPath)) {
            mkdir($moduleLogPath, 0777, true);
        }
        
        // create type path folder if not exit
        if (!file_exists($typeLogPath)) {
            mkdir($typeLogPath, 0777, true);
        }
        
        $fullFilename = sprintf("%s%s.lock", $typeLogPath, $filename);
        
        if (file_exists($fullFilename)) {
            $handle = fopen($fullFilename, 'a');
        }
        else {
            $handle = fopen($fullFilename, 'w'); 
        }
        
        fwrite($handle, $content);
        fclose($handle);

        return true;
    }
    
    public function checkLockFile($module, $filename) {
        $type = 'locks';
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('bilna_module/paymethod/log_path'));
        $moduleLogPath = sprintf("%s%s/", $baseLogPath, $module);
        $typeLogPath = sprintf("%s%s/", $moduleLogPath, $type);
        $fullFilename = sprintf("%s%s.lock", $typeLogPath, $filename);
        
        if (file_exists($fullFilename)) {
            return true;
        }
        
        return false;
    }
    
    public function removeLockFile($module, $filename) {
        $type = 'locks';
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('bilna_module/paymethod/log_path'));
        $moduleLogPath = sprintf("%s%s/", $baseLogPath, $module);
        $typeLogPath = sprintf("%s%s/", $moduleLogPath, $type);
        $fullFilename = sprintf("%s%s.lock", $typeLogPath, $filename);
        
        if (file_exists($fullFilename)) {
            unlink($fullFilename);
            
            return true;
        }
        
        return false;
    }
    
    public function checkHttpsProtocol($url) {
        $isSecure = false;
        
        if (isset ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        }
        else if (!empty ($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty ($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }
        
        $REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';
        $result = str_replace('http', $REQUEST_PROTOCOL, $url);
        
        return $result;
    }
    
    public function allowOnlyNumber($number) {
        return preg_replace('/\D/', '', trim($number));
    }
}
