<?php
class Phpro_Stockmonitor_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    { 
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        //Added this code - START  -------------------->    
        if (Mage::getSingleton('adminhtml/url')->getRequest()->getModuleName() == 'Stockmonitor') {
            $countSelect->reset(Zend_Db_Select::GROUP);
        }
        //Added this code - END -----------------------<

        $countSelect->from('', 'COUNT(DISTINCT e.entity_id)');
        $countSelect->resetJoinLeft();

        return $countSelect;
    }
}
?>