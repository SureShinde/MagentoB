<?php
class Bilna_Wrappinggiftevent_Model_Mysql4_Custom_Quote extends Mage_Core_Model_Mysql4_Abstract{

    public function _construct()
    {
        $this->_init('wrappinggiftevent/custom_quote', 'id');
    }
    
    public function deteleByQuote($quote_id){
        $table = $this->getMainTable();
        $where = $this->_getWriteAdapter()->quoteInto('quote_id = ? ', $quote_id);
        //$this->_getWriteAdapter()->quoteInto('`key` = ? 	', $var);
        $this->_getWriteAdapter()->delete($table,$where);
    }

    public function getByQuote($quote_id)
    {
        $table = $this->getMainTable();
        $where = $this->_getReadAdapter()->quoteInto('quote_id = ?', $quote_id);
        
        $sql = $this->_getReadAdapter()->select()->from($table)->where($where);
        $rows = $this->_getReadAdapter()->fetchAll($sql);
        $return = array();
        foreach($rows as $key => $val){
                $return[$key] = $val;
        }
        return $return;
    }
}