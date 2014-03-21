<?php
/**
 * Description of Bilna_Megamenu_Model_Observer_Catalog_Category
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Megamenu_Model_Observer_Catalog_Category {
    public function categorySave($observer) {
        //$params = $observer->getRequest()->getParams();
        $content = json_encode($observer->getRequest()->getParams());
        @error_log($content . "\n", 3, "/tmp/magento.log");
    }
}
