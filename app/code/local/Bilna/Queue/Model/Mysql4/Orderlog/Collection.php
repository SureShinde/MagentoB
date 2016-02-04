<?php
/**
 * Description of Bilna_Queue_Model_Mysql4_Orderlog_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 * @date 12-Nov-2015
 */

class Bilna_Queue_Model_Mysql4_Orderlog_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        $this->_init('bilna_queue/orderlog');
    }   
}
