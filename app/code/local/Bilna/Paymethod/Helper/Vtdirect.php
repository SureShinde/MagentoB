<?php
/**
 * Description of Bilna_Paymethod_Helper_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Helper_Vtdirect extends Mage_Core_Helper_Abstract {
    protected $headers = array ();
    protected $userAgent;
    protected $compression;
    protected $cookie_file;
    protected $proxy;
    protected $timeout;
    
    protected function prepareCurl() {
        //$this->headers[] = 'Connection: Keep-Alive';
        $this->headers[] = 'Accept: application/json';
        $this->headers[] = 'Content-Type: application/json';
        
        // veritrans authorization
        $serverKey = Mage::getStoreConfig('payment/vtdirect/server_key');
        $this->headers[] = 'Authorization: Basic ' . base64_encode($serverKey);
        
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        $this->compression = 'gzip';
        $this->timeout = (int) Mage::getStoreConfig('payment/vtdirect/charge_timeout');
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
        curl_setopt($process, CURLOPT_ENCODING , $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($process, CURLOPT_POST, 1);
        $response = curl_exec($process);
        curl_close($process);
        
        return $response;
    }
    
    public function removeSymbols($text) {
        $disallowedSymbol = Mage::getStoreConfig('payment/vtdirect/disallowed_symbol');
        $disallowedSymbolArr = explode(' ', $disallowedSymbol);

        return str_replace($disallowedSymbolArr, ' ', $text);
    }
    
    public function filterAddress($text) {
        return preg_replace('/[^\d\sa-z]/i', ' ' , trim($text));
    }
    
    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }
    
    public function getOrderId() {
        return $this->getCheckout()->getLastOrderId();
    }
}
