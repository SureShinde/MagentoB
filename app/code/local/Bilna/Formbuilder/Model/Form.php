<?php

class Bilna_Formbuilder_Model_Form extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('bilna_formbuilder/form');
    }

    public function findById(int $id)
    {
    	$collection = $this->getCollection();
        $fields = $this->fillable();
    	foreach ($fields as $f) {
    		$collection->addFieldToSelect($f);
    	}
		$collection->addFieldToFilter('main_table.id', $id);
		return $collection->getFirstItem();
    }

    public function fillable()
    {
        $fields = [
            'id',
            'title',
            'static_info',  
            'success_message',  
            'static_success',  
            'static_failed',  
            'force_flow',
            'url',
            'termsconditions',
            'freeproducts',
            'active_from',
            'active_to', 
            'class',
            'button_text',
            'sent_email',
            'email_id',
            'status',
            'success_redirect',
            'url_success',
            'email_share_apps',
            'social_title',
            'social_desc',
            'social_image',
            'fue',
        ];
        return $fields;
    }

    public function isFilled()
    {
        $core       = Mage::getSingleton('core/resource'); 
        $tableName  = $core->getTableName('bilna_formbuilder_flat_data_' . $this->getId());
        $connection = $core->getConnection('core_read');
        $select     = $connection->select()->from($tableName, new Zend_Db_Expr('COUNT(*)'));
        $result    = (int) $connection->fetchOne($select);
        return $result > 0;
    }
}