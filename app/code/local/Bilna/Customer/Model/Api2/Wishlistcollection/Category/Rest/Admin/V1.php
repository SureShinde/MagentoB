<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest_Admin_V1
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category/Rest/Admin/V1.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest {
    protected function _retrieveCollection() {
        $collection = Mage::getModel('socialcommerce/collectioncategory')->getCollection();

        if (!$collection && $collection->getSize() > 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        $result = [];
        $result[0] = ['total_record' => $collection->getSize()];

        foreach ($collection->load() as $collection) {
            $id = $collection->getId();
            $name = $collection->getName();
            $path = $this->_getPathByName($name);
            
            $result[$id] = [
                'id' => $id,
                'name' => $name,
                'path' => $path,
            ];
        }

        return $result;
    }

    protected function _getPathByName($name) {
        $search = [' '];
        $replace = ['-'];

        return strtolower(str_replace($search, $replace, $name));
    }
}
