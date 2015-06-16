<?php
/**
 * Description of RocketWeb_Netsuite_Model_Resource_Productcost
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class RocketWeb_Netsuite_Model_Resource_Productcost extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('rocketweb_netsuite/productcost', 'id');
    }
}
