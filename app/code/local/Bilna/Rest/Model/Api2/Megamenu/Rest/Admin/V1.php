<?php
/**
 * Description of Bilna_Rest_Model_Api2_Megamenu_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Megamenu_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Megamenu_Rest {
    protected function _retrieveCollection() {
        $result = $this->_getMegamenu();
        
        if (!$result) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $result;
    }
    
    protected function _getMegamenu() {
        $storeId = 1;
        $directory = Mage::getBaseDir() . "/files/megamenu/";
        $filename = $directory . $storeId . ".json";
        $result = array ();
        
        if (file_exists($filename)) {
            $result = json_decode(file_get_contents($filename), true);
        }
        
        return $result;
    }
}
