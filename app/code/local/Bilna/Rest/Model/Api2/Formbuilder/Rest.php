<?php
/**
 * Description of Bilna_Rest_Model_Api2_Formbuilder_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Formbuilder_Rest extends Bilna_Rest_Model_Api2_Formbuilder {
    protected $_inputSimple = array ('text', 'textarea', 'hidden', 'dropdown', 'date', 'datetime');
    protected $_inputChoice = array ('checkbox', 'radio', 'multiple');
    
    protected function _validRequired($_input, $_formData) {
        if ($_input['required'] == 1) {
            if (in_array($_input['type'], $this->_inputSimple)) {
                if (empty ($_formData[$_input['name']])) {
                    return sprintf("%s is required.", $_input['title']);
                }
                
                if (!empty ($_input['validation'])) {
                    return $this->_validFormat($_input, $_formData[$_input['name']]);
                }
            }
            elseif (in_array($_input['type'], $this->_inputChoice)) {
                if (!isset ($_formData[$_input['name']])) {
                    return sprintf("%s is required.", $_input['title']);
                }
            }
        }
        
        return true;
    }
    
    protected function _validFormat($_input, $_value) {
        $_validations = explode(" ", $_input['validation']);
        
        foreach ($_validations as $_validation) {
            if ($_validation == 'email') {
                if ($_value && filter_var($_value, FILTER_VALIDATE_EMAIL)) {
                    return true;
                }
                else {
                    return sprintf("%s does not contains a valid email address", $_input['title']);
                }
            }
            
            if ($_validation == 'number') {
                if (!is_numeric($_value)) {
                    return sprintf("%s does not contains anything other than numeric characters.", $_input['title']);
                }
            }
        }
        
        return true;
    }
    
    protected function _saveData($_formId, $_formData) {
        $_recordId = $this->_getRecordId($_formId);
        
        if (is_null($_recordId)) {
            $_recordId = 1;
        }
        
        $_rows = array ();
        
        foreach ($_formData as $k => $v) {
            if (is_array($v)) {
                $v = implode(",", $v);
            }
            
            $_rows[] = array (
                'form_id' => $_formId,
                'record_id' => $_recordId,
                'type' => $k,
                'value' => $v,
                'create_date' => $this->_getCurrentDate(),
            );
        }
        
        $_table = Mage::getSingleton('core/resource')->getTableName('bilna_formbuilder/data');
        $_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        if ($_write->insertMultiple($_table, $_rows)) {
            return true;
        }
        
        return false;
    }
    
    protected function _getRecordId($_formId) {
        $_collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
        $_collection->addFieldToSelect('record_id')->addFieldToFilter('form_id', $_formId);
        $_collection->getSelect()->order('record_id DESC')->limit(1);
        
        if ($_collection->getSize() > 0) {
            $_record = $_collection->getFirstItem();
            $_recordId = $_record->getRecordId() + 1;
            
            return (int) $_recordId;
        }
        
        return null;
    }

    protected function _getForm($_formId) {
        $_collection = Mage::getModel('bilna_formbuilder/form')->getCollection()
            ->addFieldToFilter('id', $_formId)
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('DATE(active_from)', array ('lteq' => $this->_getCurrentDate()))
            ->addFieldToFilter('DATE(active_to)', array ('gteq' => $this->_getCurrentDate()));
        $_form = $_collection->getFirstItem();
        
        if (!$_form->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $_form;
    }
    
    protected function _getInputs($_formId) {
        $_result = array ();
        $_collection = Mage::getModel('bilna_formbuilder/input')->getCollection()->addFieldToFilter('form_id', $_formId);
        $_collection->getSelect()->order('group');
        $_collection->getSelect()->order('order');
        
        if ($_collection->getSize() > 0) {
            foreach ($_collection as $_row) {
                $_result[] = array (
                    'name' => $_row->getName(),
                    'group' => $_row->getGroup(),
                    'title' => $_row->getTitle(),
                    'value' => $_row->getValue(),
                    'helper_message' => $_row->getHelperMessage(),
                    'validation' => $_row->getValidation(),
                    'type' => $_row->getType(),
                    'dbtype' => $_row->getDbtype(),
                    'required' => $_row->getRequired(),
                    'unique' => $_row->getUnique(),
                    'order' => $_row->getOrder(),
                );
            }
        }
        
        return $_result;
    }
}
