<?php
/**
 * Description of Orderdetail
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Orderdetail_Model_Orderdetail extends Mage_Core_Model_Abstract {
    protected $tblWrappingGift = 'wrappinggiftevent_order';
    
    public function getWrappingGiftOrder($orderId) {
        $read = $this->connDbRead();
        
        $sql  = "SELECT * ";
        $sql .= sprintf("FROM %s ", $this->tblWrappingGift);
        $sql .= "LIMIT 1 ";
        
        return $read->fetchAll($sql);
    }
    
    private function connDbRead() {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }
}
