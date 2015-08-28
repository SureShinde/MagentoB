<?php

class Bilna_Pricevalidation_Model_Profile extends Mage_Core_Model_Abstract
{
    protected $_defaults = array(
        'options' => array(
            'csv' => array(
                'delimiter'=>',',
                'enclosure'=>'"',
                'escape'=>'\\',
                'multivalue_separator'=>';',
            ),
            'encoding' => array(
                'from' => 'UTF-8',
                'to' => 'UTF-8',
            ),
        ),
    );
    protected $_jsonFields = array(
        'columns' => 'columns_json'
    );
    protected $_jsonImportFields = array(
        "columns"
    );

    protected function _construct()
    {
        $this->_init('bilna_pricevalidation/form');
    }

    public function factory()
    {
        $dataTypes = Mage::getSingleton('bilna_pricevalidation/config')->getDataTypes();
        $type = $this->getDataType();
        if (!$type) {
            return $this;
        }
        $model = $dataTypes->descend("$type/profile/model");
        if (!$model) {
            return $this;
        }
        $object = Mage::getModel($model);
        if (!$object) {
            Mage::throwException(Mage::helper('bilna_pricevalidation')->__('Invalid profile model: %s', $model));
        }
        $object->setData($this->getData());
        return $object;
    }

    protected function _beforeSave()
    {
        $this->_processPostData();
        $this->_serializeData();
        parent::_beforeSave();
        $this->_dataSaveAllowed = $this->_getData('title') && $this->_getData('profile_type');
    }

    protected function _processPostData()
    {
        $this->_processColumnsPost();
    }

    protected function _processColumnsPost()
    {
        if ($this->hasColumnsPost()) {
            $columns = array();
            foreach ($this->getColumnsPost() as $k=>$a) {
                foreach ($a as $i=>$v) {
                    if ($v!=='') {
                        $columns[$i][$k] = $v;
                    }
                }
            }
            $this->setColumns($columns);
        }
    }

    protected function _serializeData()
    {
        foreach ($this->_jsonFields as $k=>$f) {
            if (!is_null($this->getData($k))) {
                $this->setData($f, Zend_Json::encode($this->getData($k)));
            }
        }
    }

    protected function _unserializeData()
    {
        foreach ($this->_jsonFields as $k=>$f) {
            if (!is_null($this->getData($f))) {
                $this->setData($k, Zend_Json::decode($this->getData($f)));
            }
        }
    }

    protected function _afterLoad()
    {
        $this->_unserializeData();
        $this->_applyDefaults();
        parent::_afterLoad();
    }

    protected function _applyDefaults()
    {
        foreach ($this->_defaults as $k=>$d) {
            $this->setData($k, $this->_arrayMergeRecursive($d, (array)$this->getData($k)));
        }
    }

    public function _arrayMergeRecursive()
    {
        $params = func_get_args();
        $return = array_shift($params);
        foreach ($params as $array) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key) && (!in_array($value, $return))) {
                    if (is_array ( $value ) && isset($return[$key])) {
                        $return[] = $this->_arrayMergeRecursive($return[$key], $value);
                    } else {
                        $return[] = $value;
                    }
                } else {
                    if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
                        $return[$key] = $this->_arrayMergeRecursive($return[$key], $value);
                    } else {
                        $return[$key] = $value;
                    }
                }
            }
        }

        return $return;
    }
}
