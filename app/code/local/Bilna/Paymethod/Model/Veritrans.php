<?php
/**
 * Description of Veritrans
 *
 * @author Bilna Development Teams (development@bilna.com)
 */

class Bilna_Paymethod_Model_Veritrans extends Mage_Core_Model_Abstract
{
	protected $_connection;
    protected $_data = array ();
    protected $_table = 'veritrans_log_transaction';
    
    public function setData($data) {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                $this->_data[$key] = $value;
            }
            
            return $this;
        }
        
        return false;
    }
    
    public function addData() {
        try {
            $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_write')->beginTransaction();
            $this->_connection->insert($this->_table, $this->_data);
            
            return $this;
        }
        catch (Exception $e) {
            Mage::logException($e);
            
            return false;
        }
    }
    
    public function save() {
        try {
            $this->_connection->commit();
            
            return true;
        }
        catch (Exception $e) {
            Mage::logException($e);
            
            return false;
        }
    }
    
    public function selectData($fields) {
        try {
            $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_read');
            
            if (is_array($fields)) {
                $fields = implode(',', $fields);
            }
            
            $select = sprintf("SELECT %s ", $fields);
            $from = sprintf("FROM %s ", $this->_table);
            $where = $this->getWhere($this->_data);
            $sql = $select . $from . $where;
            
            return $this->_connection->query($sql);
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
    }
    
    public function updateData($fields) {
        $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_write')->beginTransaction();
        $this->_connection->update($this->_table, $this->_data, str_replace('WHERE ', '' , $this->getWhere($fields)));
        
        return $this;
    }
    
    protected function getWhere($fields) {
        $where = '';
        
        if (count($fields) > 0) {
            $first = true;
                
            foreach ($fields as $key => $value) {
                $value = is_string($value) ? sprintf("'%s'", $value) : $value;
                $where .= $first === true ? 'WHERE ' : 'AND ';
                $where .= sprintf("%s = %s ", $key, $value);
                $first = false;
            }
        }
        
        return $where;
    }
}