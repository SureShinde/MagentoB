<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Model_Product_Map_Value {
    const FIELD_TYPE_STANDARD = 'standard_field';
    const FIELD_TYPE_RECORD = 'record_field';
    const FIELD_TYPE_CUSTOM_SIMPLE = 'custom_simple';
    const FIELD_TYPE_CUSTOM_LIST = 'custom_list';
    const FIELD_TYPE_CUSTOM_CHECKBOX = 'custom_checkbox';

    const MAGENTO_CUSTOM_FIELD_NAME = '@__custom__';

    protected $netsuiteFieldId = null;
    protected $magentoFieldId = null;
    protected $netsuiteFieldType = null;
    protected $netsuiteListInternalId = null;
    protected $searchClassName = null;
    protected $nameField = null;
    protected $defaultValues = array();

    protected $values = array();

    public function __construct($netsuiteFieldId,$magentoFieldId,$netsuiteFieldType,$netsuiteListInternalId = null,$netsuiteFieldValue = null,$searchClassName = '',$nameField = '') {
        $this->netsuiteFieldId = $netsuiteFieldId;
        if(!$magentoFieldId) {
            $this->magentoFieldId = self::MAGENTO_CUSTOM_FIELD_NAME;
        }
        else {
            $this->magentoFieldId = $magentoFieldId;
        }
        $this->netsuiteFieldType = $netsuiteFieldType;
        $this->netsuiteListInternalId = $netsuiteListInternalId;
        $this->searchClassName = $searchClassName;
        $this->nameField = $nameField;
        $this->addDefaultValue($netsuiteFieldId,$netsuiteFieldValue);

        if(!in_array($netsuiteFieldType,array(self::FIELD_TYPE_STANDARD,self::FIELD_TYPE_CUSTOM_LIST,self::FIELD_TYPE_CUSTOM_CHECKBOX,self::FIELD_TYPE_CUSTOM_SIMPLE,self::FIELD_TYPE_RECORD))) {
            throw new Exception("Invalid Model Product Map Value Type {$netsuiteFieldType}");
        }

        if($netsuiteFieldType == self::FIELD_TYPE_CUSTOM_LIST && !$netsuiteListInternalId) {
            throw new Exception("custom_list field type needs a custom list internal id");
        }

        if($netsuiteFieldType == self::FIELD_TYPE_RECORD && (!$searchClassName)) {
            throw new Exception("record_type field needs a search class name");
        }

        if($netsuiteFieldType == self::FIELD_TYPE_RECORD && (!class_exists($searchClassName))) {
            throw new Exception("record_type field defined the search class name but the calss does not exist");
        }

        if($netsuiteFieldType == self::FIELD_TYPE_RECORD && empty($this->nameField)) {
            throw new Exception("no name field defined for search class {$searchClassName}");
        }

    }

    public function extractValue(InventoryItem $inventoryItem, $currentNetsuiteId,$currentFieldType,$replaceExiting = false) {
        if($replaceExiting) {
            $this->values = array();
        }

        switch($currentFieldType) {
            case self::FIELD_TYPE_STANDARD:
                $value = $this->extractValueFromStandardField($inventoryItem,$currentNetsuiteId);
                break;
            case self::FIELD_TYPE_RECORD:
                $value = $this->extractValueFromRecordField($inventoryItem,$currentNetsuiteId);
                break;
            case self::FIELD_TYPE_CUSTOM_SIMPLE:
                $value = $this->extractValueFromSimpleCustomField($inventoryItem,$currentNetsuiteId);
                break;
            case self::FIELD_TYPE_CUSTOM_LIST:
                $value = $this->extractValueFromListCustomField($inventoryItem,$currentNetsuiteId);
                break;
            case self::FIELD_TYPE_CUSTOM_CHECKBOX:
                $value = $this->extractValueFromCheckboxCustomField($inventoryItem,$currentNetsuiteId);
                break;
        }

        if(is_null($value)) {
            return null;
        }

        if(is_array($value)) {
            foreach($value as $val) {
                $this->values[]=$val;
            }
        }
        else {
            $this->values[] = $value;
        }

        Mage::dispatchEvent('product_map_value_extracted',array('product_map_value'=>$this));
    }

    protected function extractValueFromStandardField(InventoryItem $inventoryItem,$currentNetsuiteId) {
        if(isset($inventoryItem->{$currentNetsuiteId})) return $inventoryItem->{$currentNetsuiteId};
        else return null;
    }

    protected function extractValueFromSimpleCustomField(InventoryItem $inventoryItem,$currentNetsuiteId) {
        $fields = $this->getCustomFields($inventoryItem);

        foreach($fields as $customField) {
            if($customField->internalId ==  $currentNetsuiteId) {
                if($customField->value instanceof ListOrRecordRef) return $customField->value->name;
                if($customField instanceof DateCustomFieldRef) {
                    $value = new DateTime($customField->value);
                    return $value->format('Y-m-d H:i:s');
                }
                return $customField->value;
            }
        }
        return null;
    }

    protected function extractValueFromListCustomField(InventoryItem $inventoryItem,$currentNetsuiteId) {
        $fields = $this->getCustomFields($inventoryItem);
        foreach($fields as $customField) {
            if($customField->internalId ==  $currentNetsuiteId) {
                if($customField->value instanceof ListOrRecordRef) {
                    return Mage::helper('rocketweb_netsuite')->getListValue($customField->value->typeId,$customField->value->internalId);
                }
                elseif(is_array($customField->value)) {
                    $values = array();
                    foreach($customField->value as $listValue) {
                        $values[]=Mage::helper('rocketweb_netsuite')->getListValue($listValue->typeId,$listValue->internalId);
                    }
                    return $values;
                }
                else return $customField->value;
            }
        }
        return null;
    }

    protected function extractValueFromRecordField(InventoryItem $inventoryItem,$currentNetsuiteId) {
        $fields = $this->getCustomFields($inventoryItem);
        foreach($fields as $customField) {
            if($customField->internalId ==  $currentNetsuiteId) {
                if($customField->value instanceof ListOrRecordRef) {
                    return Mage::helper('rocketweb_netsuite')->getRecordListItem($this->searchClassName,$this->nameField,$customField->value->internalId);
                }
                elseif(is_array($customField->value)) {
                    $values = array();
                    foreach($customField->value as $listValue) {
                        $values[]=Mage::helper('rocketweb_netsuite')->getRecordListItem($this->searchClassName,$this->nameField,$customField->value->internalId);
                    }
                    return $values;
                }
                else return $customField->value;
            }
        }
        return null;
    }

    protected function extractValueFromCheckboxCustomField(InventoryItem $inventoryItem,$currentNetsuiteId) {
        $fields = $this->getCustomFields($inventoryItem);

        foreach($fields as $customField) {
            if($customField->internalId ==  $currentNetsuiteId) {
                if($customField->value) {
                    return $this->getDefaultValue($currentNetsuiteId);
                }
                else {
                    return null;
                }
            }
        }
        return null;
    }

    protected function getCustomFields(InventoryItem $inventoryItem) {
        $fields = array();

        if($inventoryItem->customFieldList && is_array($inventoryItem->customFieldList->customField)) {
            foreach($inventoryItem->customFieldList->customField as $field) {
                $fields[]=$field;
            }
        }
        if($inventoryItem->matrixOptionList && is_array($inventoryItem->matrixOptionList->matrixOption)) {
            foreach($inventoryItem->matrixOptionList->matrixOption as $field) {
                $fields[]=$field;
            }
        }

        return $fields;
    }

    public function getValue() {
        return $this->values;
    }
    public function getValues() {
        return $this->values;
    }
    public function setValues($value) {
        $this->values = $value;
    }
    public function setValue($value) {
        $this->values = $value;
    }

    public function getNetsuiteFieldId() {
        return $this->netsuiteFieldId;
    }

    public function getNetsuiteFieldType() {
        return $this->netsuiteFieldValue;
    }

    public function getMagentoFieldId() {
        return $this->magentoFieldId;
    }


    public static function getCustomValueListKey($magentoFieldName) {
        static $counter = 0;

        if(trim($magentoFieldName)) {
            return trim($magentoFieldName);
        }
        else {
            $counter++;
            return self::MAGENTO_CUSTOM_FIELD_NAME.$counter;
        }
    }

    public function addDefaultValue($netsuiteId,$value) {
        $this->defaultValues[$netsuiteId] = $value;
    }
    public function getDefaultValue($netsuiteId) {
        return $this->defaultValues[$netsuiteId];
    }

}