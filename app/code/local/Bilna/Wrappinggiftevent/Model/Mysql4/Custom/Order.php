<?php
class Bilna_Wrappinggiftevent_Model_Mysql4_Custom_Order extends Mage_Core_Model_Mysql4_Abstract{
    
    public function _construct()
    {
        $this->_init('wrappinggiftevent/custom_order', 'id');
    }
    
    public function deteleByOrder($order_id,$var)
    {
        $table = $this->getMainTable();
        $where = $this->_getWriteAdapter()->quoteInto('order_id = ?', $order_id);
        //$this->_getWriteAdapter()->quoteInto('`key` = ? 	', $var);
        $this->_getWriteAdapter()->delete($table,$where);
    }
    
    public function getByOrder($order_id)
    {
        $table = $this->getMainTable();
        $where = $this->_getReadAdapter()->quoteInto('order_id = ?', $order_id);
        
        $sql = $this->_getReadAdapter()->select("wrapping_price")->from($table)->where($where);
        $rows = $this->_getReadAdapter()->fetchRow($sql);

        return $rows;
    }

    public function loadByOrderId($transaction, $order_id) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('order_id = ?', $order_id);
        $data = $this->_getReadAdapter()->fetchRow($select);
        if (isset($data['id'])) {
            $transaction->load($data['id'])->addData($data);
        }
        return $this;
    }
}