<?php

class Bilna_Formbuilder_Model_Input extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('bilna_formbuilder/input');
    }

    //Fungsi Date of Birth (DOB)
    public function _getDateDropdown($year_limit = 0)
    {
    	$datedropdown = Mage::getModel('bilna_formbuilder/input')->_getDateDropdown($year_limit = 0);
    }

    public function addUnique()
    {
    	
    }

    public function findByParent($parentId)
    {
        $collection = $this->getCollection();
        $fields = [
            'name',
            'group',
            'title',
            'type',
            'unique',
            'order',
            'helper_message',
            'required',
            'value',
            'validation',
            'dbtype',
        ];
        foreach ($fields as $f) {
            $collection->addFieldToSelect($f);
        }

        $collection->addFieldToFilter('main_table.id', $parentId);
        return $collection->getFirstItem();
    }

}