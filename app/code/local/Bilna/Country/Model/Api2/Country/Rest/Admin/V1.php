<?php

class Bilna_Country_Model_Api2_Country_Rest_Admin_V1 extends Bilna_Country_Model_Api2_Country_Rest {
    protected function _retrieveCollection() {
        $result = $this->_getRetrieveCollection();
        
        if (!$result) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $result;
    }
    
    protected function _getRetrieveCollection() {
        $result = array ();
        $countryId = $this->getRequest()->getParam('country_id');
        
        if (!$countryId) {
            $this->_critical(self::RESOURCE_DATA_INVALID);
        }
        
        if ($regionId = $this->getRequest()->getParam('region_id')) {
            $type = 'city';
            $collection = Mage::getModel('customercity/customercity')->getResourceCollection();
            $collection->addFieldToFilter('country', $countryId);
            $collection->addFieldToFilter('state', array ('like' => $regionId . '%'));
        }
        else {
            $type = 'province';
            $collection = Mage::getModel('directory/region')->getResourceCollection();
            $collection->addCountryFilter($countryId);
        }
        
        if ($collection->getSize() > 0) {
            foreach ($collection->getData() as $row) {
                if ($type == 'city') {
                    $result[] = array (
                        'city_id' => $row['id'],
                        'city' => $row['city'],
                    );
                }
                else {
                    $result[] = array (
                        'region_id' => $row['region_id'],
                        'state' => $row['name'],
                    );
                }
            }
        }
        
        return $result;
        
        
        //get province params: country_id. get city params: country_id, region_id, city
    }
}
