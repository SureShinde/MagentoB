<?php

abstract class Bilna_Country_Model_Api2_Country_Rest extends Bilna_Country_Model_Api2_Country
{

    /**
     * Get customers list
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $data = $this->_getCollectionForRetrieve()
            ->load()
            ->toArray();
        
        return isset($data['items']) ? $data['items'] : $data;
    }

    /**
     * Retrieve collection instances
     *
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        /**
         *
         * @var $collection Mage_Customer_Model_Resource_Customer_Collection
         */
        $collection = Mage::getModel('customercity/customercity')->getCollection();
        // $collection = Mage::getResourceModel('directory/region_collection');
        $collection->getSelect()
            ->join(array(
            "a" => "directory_country_region"
        ), "a.default_name = main_table.state", array(
            "country_id",
            "region_id",
            "code",
            "default_name"
        ))
            ->group('main_table.city');
        
        // $collection->addAttributeToSelect(array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)));
        $this->_applyCollectionModifiers($collection);
        
        return $collection;
    }
}
