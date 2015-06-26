<?php
/**
 * Description of Bilna_Formbuilder_Model_Api2_Form_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Model_Api2_Form_Rest extends Bilna_Formbuilder_Model_Api2_Form {
    public function _retrieve() {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->_loadFormbuilderFormById($id);
        $collectionData = $collection->getData();
        
        return $collectionData;
    }
    
    protected function _loadFormbuilderFormById($id) {
        $collection = Mage::getModel('bilna_formbuilder/form')->load($id);
        
        if (!$collection->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $collection;
    }
}