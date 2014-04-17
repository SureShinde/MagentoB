<?php
/**
 * Description of Bilna_Megamenu_Model_Catalog_Category
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Megamenu_Model_Catalog_Category extends Mage_Catalog_Model_Category {
    /**
     * Retrieve megamenu_image URL
     *
     * @return string
     */
    public function getMegamenuImageUrl($image) {
        $url = false;
        
        if ($image) {
            $url = Mage::getBaseUrl('media') . 'catalog/category/' . $image;
        }
        
        return $url;
    }
}
