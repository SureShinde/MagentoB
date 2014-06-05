<?php
/**
 * Description of webservice_asidisclaimer_savecookie.php
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Mage_Catalog_Model_Webservice_Asidisclaimer extends Mage_Catalog_Model_Abstract {
    public function saveCookieAction($name, $value) {
        $cookie = Mage::getModel('core/cookie');
        
        if ($cookie->set($name, $value)) {
            return true;
        }
        
        return false;
    }
}
