<?php
/**
 * Description of Bilna_Paymethod_Block_Checkout_Onepage
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage {
    public function getUrl($route = '', $params = array ()) {
        $url = Mage::helper('paymethod')->checkHttpsProtocol($this->_getUrlModel()->getUrl($route, $params));
        
        return $url;
    }
}
