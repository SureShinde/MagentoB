<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 * EAV attribut resource
 */
class Belvg_Brands_Model_Resource_Eav_Mysql4_Brands extends Belvg_Brands_Model_Resource_Abstract
{

    /**
     * Init attribute recource
     */
    public function _construct()
    {
        $resource = Mage::getSingleton('core/resource');
        /* @var $installer Mage_Core_Model_Resource_Resource */

        $this->setType('brands');
        $this->setConnection(
                $resource->getConnection('brands_read'), $resource->getConnection('brands_write')
        );
    }

    /**
     * Load attribute model by code
     *
     * @param Mage_Core_Model_Abstract $object
     * @param int $entityTypeId
     * @param string $code
     * @return boolean
     */
    public function loadByCode(Mage_Core_Model_Abstract $object, $entityTypeId, $code)
    {
        $bind = array(':entity_type_id' => $entityTypeId);
        $select = $this->_getLoadSelect('attribute_code', $code, $object)
                ->where('entity_type_id = :entity_type_id');
        $data = $this->_getReadAdapter()->fetchRow($select, $bind);

        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Select brand identifier
     *
     * @param string $identifier
     * @param int $storeId
     * @return Varien_Object
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID, $storeId);
        $select = $this->_getReadAdapter()->select()
                ->from(array('cp' => $this->getTable('brands/brands')))
                ->joinLeft(array('a' => $this->getTable('eav/attribute')), 'a.attribute_code = "url_key"', array())
                ->joinLeft(array('cps' => $this->getTable(array('brands/brands', 'varchar'))), 'cp.entity_id = cps.entity_id AND cps.attribute_id = a.attribute_id', array())
                ->where('cps.value = ?', $identifier)
                ->where('cps.store_id IN (?)', $stores);


        $select->reset(Zend_Db_Select::COLUMNS)
                ->columns('cp.entity_id')
                ->order('cps.store_id DESC')
                ->limit(1);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get table name from allias
     *
     * @param string|array $modelEntity
     * @return string
     */
    public function getTable($modelEntity)
    {
        $tableSuffix = NULL;
        if (is_array($modelEntity)) {
            list($modelEntity, $tableSuffix) = $modelEntity;
        }

        $tableName = Mage::getSingleton('core/resource')->getTableName($modelEntity);
        if (!is_null($tableSuffix)) {
            $tableName .= '_' . $tableSuffix;
        }

        return $tableName;
    }

    /**
     * Get attribute values
     *
     * @param string $field
     * @param string $value
     * @param Mage_Core_Model_Abstract $object
     * @return Varien_Object
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $field = $this->_getReadAdapter()->quoteIdentifier(sprintf('%s.%s', 'eav_attribute', $field));
        $select = $this->_getReadAdapter()->select()
                ->from('eav_attribute')
                ->where($field . '=?', $value);
        return $select;
    }

}