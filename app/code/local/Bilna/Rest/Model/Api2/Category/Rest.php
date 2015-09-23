<?php
/**
 * Description of Bilna_Rest_Model_Api2_Category_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Category_Rest extends Bilna_Rest_Model_Api2_Category {
    protected function _getCategoryImageUrl($_image) {
        return sprintf("%scatalog/category/%s", Mage::getBaseUrl('media'), $_image);
    }
}
