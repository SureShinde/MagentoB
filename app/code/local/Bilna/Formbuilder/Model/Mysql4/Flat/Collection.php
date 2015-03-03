<?php
/**
 * Description of Bilna_Formbuilder_Model_Mysql4_Flat_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Model_Mysql4_Flat_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected $tablePrefix = 'bilna_formbuilder_flat_data_';
    
    public function _construct() {
        $this->_init('bilna_formbuilder/flat');
    }
    
    public function getMainTable() {
        $formId = Mage::app()->getRequest()->getParam('id');
        
        return $this->tablePrefix . $formId;
    }
}
