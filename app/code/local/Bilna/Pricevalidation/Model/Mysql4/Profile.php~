<?php

class Bilna_Pricevalidation_Model_Mysql4_Profile extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('bilna_pricevalidation/form', 'profile_id');
    }

    /*public function sync(Mage_Core_Model_Abstract $object, $saveFields=null, $loadFields=null)
    {
        $conn = $this->_getWriteAdapter();
        $table = $this->getMainTable();

        $condition = $conn->quoteInto($this->getIdFieldName().'=?', $object->getId());

        if ($saveFields) {
            $saveData = array();
            foreach ($saveFields as $k) {
                $saveData[$k] = $object->getData($k);
            }
            $conn->update($table, $saveData, $condition);
        }

        if ($loadFields) {
            $loadData = $conn->fetchRow($conn->select()->from($table, $loadFields)->where($condition));
            foreach ($loadData as $k=>$v) {
                $object->setData($k, $v);
            }
        }

        return $this;
    }*/
}
