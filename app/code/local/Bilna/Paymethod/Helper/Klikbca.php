<?php
/**
 * Description of Bilna_Paymethod_Helper_Klikbca
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Helper_Klikbca extends Mage_Core_Helper_Abstract {
    protected $headers = array ();
    protected $userAgent;
    protected $compression;
    protected $cookie_file;
    protected $proxy;
    protected $timeout;
    
    protected function prepareCurl() {
        $this->headers[] = 'Connection: Keep-Alive';
        $this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        $this->compression = 'gzip';
        $this->timeout = Mage::getStoreConfig('payment/klikbca/confirm_timeout');
    }
    
    /**
     * @param string $url
     * @return type
     */
    public function getRequest($url) {
        $this->prepareCurl();
        
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        //if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        //if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($process, CURLOPT_ENCODING , $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, $this->timeout);
        //if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($process);
        curl_close($process);
        
        return $response;
    }
    
    /**
     * 
     * @param string $url
     * @param array $data
     * @return type
     */
    public function postRequest($url, $data) {
        $this->prepareCurl();
        
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        //if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        //if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($process, CURLOPT_ENCODING , $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, $this->timeout);
        //if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($process, CURLOPT_POST, 1);
        $response = curl_exec($process);
        curl_close($process);
        
        return $response;
    }
    
    /**
     * @param string $path
     * @param string $filename
     * @param string $content
     * @param string $type = debug|normal
     * @return boolean
     */
    public function _writeLogFile($path, $filename, $content, $type = 'debug') {
        if ($type == 'debug') {
            $currDateMagento = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
            $content = sprintf("%s DEBUG: %s", $currDateMagento, $content);
        }
        
        $baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('payment/klikbca/base_log_path'));
        
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
    
    /**
     * @param string $filename
     * @return boolean
     */
    public function _createLockFile($filename) {
        $baseLockPath = sprintf("%s/locks/", Mage::getBaseDir('var'));
        $fullFilename = sprintf("%s%s.lock", $baseLockPath, $filename);
        
        $handle = fopen($fullFilename, 'w');
        fwrite($handle, date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
        fclose($handle);
        
        return true;
    }
    
    public function _checkLockFile($filename) {
        $baseLockPath = sprintf("%s/locks/", Mage::getBaseDir('var'));
        $fullFilename = sprintf("%s%s.lock", $baseLockPath, $filename);
        
        if (file_exists($fullFilename)) {
            return true;
        }
        
        return false;
    }
    
    public function _removeLockFile($filename) {
        $baseLockPath = sprintf("%s/locks/", Mage::getBaseDir('var'));
        $fullFilename = sprintf("%s%s.lock", $baseLockPath, $filename);
        
        if (file_exists($fullFilename)) {
            unlink($fullFilename);
            
            return true;
        }
        
        return false;
    }
    
    public function _createLockFileTransaction($filename, $status = 'success') {
        $lock = $this->_getLockFileTransaction($status);
        $base = $lock['base'];
        $path = $lock['path'];
        
        // create base log path folder if not exit
        if (!file_exists($base)) {
            mkdir($base, 0777, true);
        }
        
        // create path folder if not exit
        if (!file_exists($base . $path)) {
            mkdir($base . $path, 0777, true);
        }
        
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $tdatetime = date('Ymd H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $fullFilename = sprintf("%s%s%s.lock", $base, $path, $filename);
        
        $handle = fopen($fullFilename, 'w');
        fwrite($handle, $tdatetime . "\n");
        fclose($handle);

        return true;
    }
    
    public function _checkLockFileTransaction($filename, $status = 'success') {
        $lock = $this->_getLockFileTransaction($status);
        $base = $lock['base'];
        $path = $lock['path'];
        $fullFilename = sprintf("%s%s%s.lock", $base, $path, $filename);
        
        if (file_exists($fullFilename)) {
            return true;
        }
        
        return false;
    }
    
    public function _removeLockFileTransaction($filename, $status = 'failed') {
        $lock = $this->_getLockFileTransaction($status);
        $base = $lock['base'];
        $path = $lock['path'];
        $fullFilename = sprintf("%s%s%s.lock", $base, $path, $filename);
        
        if (file_exists($fullFilename)) {
            unlink($fullFilename);
            
            return true;
        }
        
        return false;
    }
    
    private function _getLockFileTransaction($status) {
        $result = array (
            'base' => sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('payment/klikbca/base_log_path')),
            'path' => Mage::getStoreConfig(sprintf("payment/klikbca/%s_lock_path", $status))
        );
        
        return $result;
    }
    
    /**
     * 
     * @param int $templateId
     * @param array $emailVars
     * @return boolean
     */
    public function _sendEmail($templateId, $emailVars) {
        $emailSender = array (
            'name' => 'Bilna.com',
            'email' => 'cs@bilna.com'
        );
        
        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');
        $sendEmail = Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $emailSender, $emailVars['email_to'], $emailVars['name_to'], $emailVars, $storeId);
        $translate->setTranslateInline(true);

        if ($sendEmail) {
            return true;
        }

        return false;
    }
    
    /**
     * 
     * @param string $text
     * @return string
     */
    public function _removeSymbols($text) {
        $disallowed_symbol = '~ / \ < > + = ( ) * & ^ % $ £ @ ! ± ? " #';
        $disallowed_symbol_arr = explode(' ', $disallowed_symbol);

        return str_replace($disallowed_symbol_arr, ' ', $text);
    }
}
