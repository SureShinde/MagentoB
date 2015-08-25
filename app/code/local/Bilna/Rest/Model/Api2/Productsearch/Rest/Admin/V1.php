<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productsearch_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Productsearch_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Productsearch_Rest {
    protected function _retrieveCollection() {
        $query = $this->_getQuery();
        $query->setStoreId($this->_getStore()->getId());
        
        if ($query->getQueryText() != '') {
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            }
            else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity() + 1);
                }
                else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()) {
                    $query->save();
                    echo $query->getRedirect();exit;
                }
                else {
                    $query->prepare();
                }
            }
        }
        else {
            
        }
    }
}
