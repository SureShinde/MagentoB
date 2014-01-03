<?php
/**
 * Description of Bilna_Paymethod_Model_Observer_Klikbca
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer_Klikbca {
    protected $_code = 'klikbca';
    protected $_baseLogPath = '';
    protected $_confirmLogPath = '';
    protected $_logPath = '';

    protected function setPath() {
        $this->_baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('bilna_module/paymethod/log_path'));
        $this->_confirmLogPath = Mage::getStoreConfig('payment/klikbca/confirm_log_path');
        $this->_logPath = $this->_baseLogPath . $this->_confirmLogPath;
    }
    
    public function confirmationProcess() {
        $this->setPath();
        /**
         * read directory
         */
        if ($handle = opendir($this->_logPath)) {
            while (false !== ($orderFile = readdir($handle))) {
                if ($orderFile != "." && $orderFile != "..") {
                    $this->orderProcess($orderFile);
                }
            }

            closedir($handle);
        }
    }
    
    private function orderProcess($orderFile) {
        $filename = $this->_logPath . $orderFile;
        $orderDetail = file($filename, FILE_IGNORE_NEW_LINES);
        
        if ($orderDetail !== false) {
            $orderDetail = $orderDetail[0];
            $crUrl = Mage::getStoreConfig('payment/klikbca/confirm_url');
            $crUsername = Mage::getStoreConfig('payment/klikbca/confirm_username');
            $crPassword = Mage::getStoreConfig('payment/klikbca/confirm_password');
            $klikbcaUserId = $orderDetail[0];
            $transactionNo = $orderDetail[1];
            $transactionDate = $orderDetail[2];
            $transactionAmount = $orderDetail[3];
            $type = $orderDetail[4];
            $additionalData = $orderDetail[5];
            $logDate = $orderDetail[6];
            $rettype = 'xml';
            
            $crData = array (
                'cru' => $crUsername,
                'crp' => $crPassword,
                'userid' => $klikbcaUserId,
                'transno' => $transactionNo,
                'transdate' => $transactionDate,
                'amount' => $transactionAmount,
                'type' => $type,
                'adddata' => $additionalData,
                'rettype' => $rettype
            );
            
            $response = Mage::helper('paymethod/klikbca')->postRequest($crUrl, $crData);
            
            //["DHANYALVIAN|300000007|01\/02\/2014 23:11:00|IDR187200.00|N||2014-01-03 06:11:53"]
        }
    }
}
