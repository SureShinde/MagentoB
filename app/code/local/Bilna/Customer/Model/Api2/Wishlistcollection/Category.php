<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Wishlistcollection_Category extends Bilna_Customer_Model_Api2_Wishlistcollection {
    protected function _getPathByName($name) {
        $search = [' '];
        $replace = ['-'];

        return strtolower(str_replace($search, $replace, $name));
    }

    protected function _getNameByPath($path) {
        $search = ['-'];
        $replace = [' '];

        return strtolower(str_replace($search, $replace, $path));
    }
}
