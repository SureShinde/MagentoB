<?php
class Bilna_Wrappinggiftevent_Model_Mysql4_Wrappinggiftevent_List extends Mage_Core_Model_Mysql4_Abstract{
    
    public function _construct()
    {
        $this->_init('wrappinggiftevent/wrapping_gift_event', 'id');
    }
    
    public function getWrapLists()
    {
        $table = $this->getMainTable();
        
        $sql = $this->_getReadAdapter()->select("wrapping_price")->from($table);
        $result = $this->_getReadAdapter()->fetchAll($sql);

        return $result;
    }
}