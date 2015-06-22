<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Customerattributes_Model_Resource_Value extends Mage_Core_Model_Mysql4_Abstract
{

    const INT_TYPE = 'int';
    const VARCHAR_TYPE = 'varchar';
    const TEXT_TYPE = 'text';
    const DATE_TYPE = 'date';

    private $_mainTableValue = self::INT_TYPE;

    private $_additionalValueTables = array(
        self::VARCHAR_TYPE => 'aw_customerattributes/value_varchar',
        self::TEXT_TYPE    => 'aw_customerattributes/value_text',
        self::DATE_TYPE    => 'aw_customerattributes/value_date'
    );

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('aw_customerattributes/value_int', 'value_id');
    }

    /**
     * @return array
     */
    public function getAllTables()
    {
        $tables = array();
        $tables[$this->_mainTableValue] = $this->getMainTable();
        foreach ($this->_additionalValueTables as $key => $table) {
            $tables[$key] = $this->getTable($table);
        }
        return $tables;
    }

    /**
     * @param Zend_Db_Select $select
     *
     * @return Zend_Db_Select[]
     */
    public function getSelectsFromMainSelect(Zend_Db_Select $select)
    {
        $selectArr = array();
        foreach ($this->getAllTables() as $table) {
            $newSelect = clone $select;
            $newSelect->reset(Zend_Db_Select::COLUMNS);
            $newSelect->reset(Zend_Db_Select::FROM);
            $newSelect->from(array('main_table' => $table));
            $selectArr[] = $newSelect;
        }
        return $selectArr;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string                   $field
     * @param mixed                    $value
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $selectArr = $this->getSelectsFromMainSelect($select);
        $gSelect = $this->_getReadAdapter()->select();
        $gSelect->union($selectArr);
        return $gSelect;
    }

    /**
     * Save object object data
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->_serializeFields($object);
        $this->_beforeSave($object);
        $this->_checkUnique($object);
        if (!is_null($object->getId()) && (!$this->_useIsObjectNew || !$object->isObjectNew())) {
            $condition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . '=?', $object->getId());
            /**
             * Not auto increment primary key support
             */
            if ($this->_isPkAutoIncrement) {
                $data = $this->_prepareDataForSave($object);
                unset($data[$this->getIdFieldName()]);
                $this->_getWriteAdapter()->update($this->_getTableByValue($object), $data, $condition);
            } else {
                $select = $this->_getWriteAdapter()->select()
                    ->from($this->_getTableByValue($object, array($this->getIdFieldName())))
                    ->where($condition);
                if ($this->_getWriteAdapter()->fetchOne($select) !== false) {
                    $data = $this->_prepareDataForSave($object);
                    unset($data[$this->getIdFieldName()]);
                    if (!empty($data)) {
                        $this->_getWriteAdapter()->update($this->_getTableByValue($object), $data, $condition);
                    }
                } else {
                    $this->_getWriteAdapter()->insert(
                        $this->_getTableByValue($object), $this->_prepareDataForSave($object)
                    );
                }
            }
        } else {
            $bind = $this->_prepareDataForSave($object);
            if ($this->_isPkAutoIncrement) {
                unset($bind[$this->getIdFieldName()]);
            }
            $this->_getWriteAdapter()->insert($this->_getTableByValue($object), $bind);

            $object->setId($this->_getWriteAdapter()->lastInsertId($this->_getTableByValue($object)));

            if ($this->_useIsObjectNew) {
                $object->isObjectNew(false);
            }
        }

        $this->unserializeFields($object);
        $this->_afterSave($object);

        return $this;
    }

    /**
     * Forsed save object data
     * forsed update If duplicate unique key data
     *
     * @deprecated
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function forsedSave(Mage_Core_Model_Abstract $object)
    {
        $this->_beforeSave($object);
        $bind = $this->_prepareDataForSave($object);
        $adapter = $this->_getWriteAdapter();
        // update
        if (!is_null($object->getId()) && $this->_isPkAutoIncrement) {
            unset($bind[$this->getIdFieldName()]);
            $condition = $adapter->quoteInto($this->getIdFieldName() . '=?', $object->getId());
            $adapter->update($this->_getTableByValue($object), $bind, $condition);
        } else {
            $adapter->insertOnDuplicate($this->_getTableByValue($object), $bind, $this->_fieldsForUpdate);
            $object->setId($adapter->lastInsertId($this->_getTableByValue($object)));
        }

        $this->_afterSave($object);

        return $this;
    }

    /**
     * Delete the object
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function delete(Mage_Core_Model_Abstract $object)
    {
        $this->_beforeDelete($object);
        $this->_getWriteAdapter()->delete(
            $this->_getTableByValue($object),
            $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . '=?', $object->getId())
        );
        $this->_afterDelete($object);
        return $this;
    }

    /**
     * Prepare data for save
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return array
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        return $this->_prepareDataForTable($object, $this->_getTableByValue($object));
    }

    /**
     * Check that model data fields that can be saved
     * has really changed comparing with origData
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return boolean
     */
    public function hasDataChanged($object)
    {
        if (!$object->getOrigData()) {
            return true;
        }

        $fields = $this->_getWriteAdapter()->describeTable($this->_getTableByValue($object));
        foreach (array_keys($fields) as $field) {
            if ($object->getOrigData($field) != $object->getData($field)) {
                return true;
            }
        }

        return false;
    }

    protected function _checkUnique(Mage_Core_Model_Abstract $object)
    {
        //no unique support
        return $this;
    }

    /**
     * help function
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return string
     * @throws Exception
     */
    private function _getTableByValue(Mage_Core_Model_Abstract $object)
    {
        if ($object->getValueType() === $this->_mainTableValue) {
            return $this->getMainTable();
        } elseif (isset($this->_additionalValueTables[$object->getValueType()])) {
            return $this->getTable($this->_additionalValueTables[$object->getValueType()]);
        } else {
            throw new Exception('Unknown value type');
        }
    }
}