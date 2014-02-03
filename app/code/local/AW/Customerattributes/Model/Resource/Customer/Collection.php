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

class AW_Customerattributes_Model_Resource_Customer_Collection
    extends Mage_Customer_Model_Entity_Customer_Collection
{
    public function addFieldToFilter($field, $condition = null)
    {
        $attribute = Mage::getModel('aw_customerattributes/attribute')->loadByCode($field);
        if ($attribute->getId()) {
            $valueType = $attribute->getTypeModel()->getValueType();
            $defaultValue = str_replace(array("\r", "\n"), ' ', $attribute->getDefaultValue());
            $fieldValue = "IFNULL({$attribute->getCode()}_{$valueType}.value, '{$defaultValue}')";
            if (isset($condition['like'])) {
                $this->getSelect()->where($fieldValue . ' LIKE ?', $condition['like']);
            } elseif (isset($condition['from']) || isset($condition['to'])) {
                $fromToSql = '';
                if (isset($condition['from'])) {
                    $from = $this->getConnection()->convertDate($condition['from']);
                    $fromToSql .= $this->getConnection()->quoteInto("$fieldValue >= ?", $from);
                }
                if (isset($condition['to'])) {
                    $fromToSql .= empty($fromToSql) ? '' : ' AND ';
                    $to = $this->getConnection()->convertDate($condition['to']);
                    $fromToSql .= $this->getConnection()->quoteInto("$fieldValue <= ?", $to);
                }
                $this->getSelect()->where($fromToSql);
            } elseif (isset($condition['eq'])) {
                if ($attribute->getType() == 'multipleselect') {
                    $this->getSelect()->where('FIND_IN_SET(?, ' . $fieldValue . ')', (int)$condition['eq']);
                } else {
                    $this->getSelect()->where($fieldValue . ' = ?', $condition['eq']);
                }
            }
            return $this;
        }
        parent::addFieldToFilter($field, $condition);
        return $this;
    }

    /**
     * @return AW_Customerattributes_Model_Resource_Customer_Collection
     */
    public function addAttributesToCustomerCollection()
    {
        $attributesCollection = Mage::helper('aw_customerattributes/customer')->getAttributeCollectionForCustomerGrid();
        foreach ($attributesCollection as $attribute) {
            $valueType = $attribute->getTypeModel()->getValueType();
            $conditions = array(
                "{$attribute->getCode()}_{$valueType}.customer_id = e.entity_id",
                "{$attribute->getCode()}_{$valueType}.attribute_id = {$attribute->getId()}",
            );
            $this->getSelect()->joinLeft(
                array("{$attribute->getCode()}_{$valueType}" => "aw_customerattributes_value_{$valueType}"),
                join(' AND ', $conditions),
                array()
            );
            $defaultValue = preg_split('/\r\n|\r|\n/', $attribute->getDefaultValue());
            $fieldValue = "IFNULL({$attribute->getCode()}_{$valueType}.value, '{$defaultValue[0]}')";
            $this->getSelect()->columns(array($attribute->getCode() => $fieldValue));
        }
        return $this;
    }

    /**
     * @param string $attribute
     * @param string $dir
     *
     * @return AW_Customerattributes_Model_Resource_Customer_Collection|Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addAttributeToSort($attribute, $dir = 'asc')
    {
        $attributeModel = Mage::getModel('aw_customerattributes/attribute')->loadByCode($attribute);
        if (is_null($attributeModel->getId())) {
            return parent::addAttributeToSort($attribute, $dir);
        }
        $valueType = $attributeModel->getTypeModel()->getValueType();
        $defaultValue = str_replace(array("\r", "\n"), ' ', $attributeModel->getDefaultValue());
        $fieldValue = "IFNULL({$attributeModel->getCode()}_{$valueType}.value, '{$defaultValue}')";
        $this->getSelect()->order("{$fieldValue} {$dir}");
        return $this;
    }

}