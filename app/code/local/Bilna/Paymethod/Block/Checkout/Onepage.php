<?php
/**
 * Description of Bilna_Paymethod_Block_Checkout_Onepage
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage {
    public function getUrl($route = '', $params = array ()) {
        return $this->checkHttpsProtocol($this->_getUrlModel()->getUrl($route, $params));
    }
    
    protected function checkHttpsProtocol($url) {
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
}
