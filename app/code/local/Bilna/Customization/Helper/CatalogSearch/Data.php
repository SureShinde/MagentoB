<?php

class Bilna_Customization_Helper_CatalogSearch_Data extends Mage_CatalogSearch_Helper_Data
{

    /**
     * Retrieve query model object
     *
     * Patched to temporarily disable synonym functionality until proper indexing is put in place
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getQuery()
    {
        if (!$this->_query) {
            $this->_query = Mage::getModel('catalogsearch/query')
                ->loadByQueryText($this->getQueryText());
            if (!$this->_query->getId()) {
                $this->_query->setQueryText($this->getQueryText());
            }
        }
        return $this->_query;
    }

}