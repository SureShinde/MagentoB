<?php
/**
 * Description of Bilna_Rest_Model_Api2_Formbuilder_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Formbuilder_Rest extends Bilna_Rest_Model_Api2_Formbuilder {
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
        
        return $_form->getData();
    }
    
    protected function _getInputs($_formId) {
        $_result = array ();
        $_collection = Mage::getModel('bilna_formbuilder/input')->getCollection()->addFieldToFilter('form_id', $_formId);
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
