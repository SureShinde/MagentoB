<?php
/**
 * Description of Bilna_Formbuilder_Model_Api2_Input_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Model_Api2_Input_Rest extends Bilna_Formbuilder_Model_Api2_Input {
    public function _retrieve() {
        $formId = $this->getRequest()->getParam('formid');
        $collection = $this->_loadFormbuilderInputById($formId);
        $collectionData = $collection->getData();
        
        return $collectionData;
    }
    
    protected function _loadFormbuilderInputById($formId) {
        $collection = Mage::getModel('bilna_formbuilder/input')->getCollection();
        $collection->getSelect()->join('bilna_formbuilder_input', 'main_table.id = bilna_formbuilder_input.form_id');
        $collection->addFieldToFilter('main_table.id', $formId)
            ->addOrder('bilna_formbuilder_input.order', 'ASC')
            ->addOrder('bilna_formbuilder_input.group', 'ASC');
        
        if ($collection->getSize() == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $collection;
    }
}