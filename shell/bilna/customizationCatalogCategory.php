<?php
/**
 * Description of customizationCatalogCategory
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class CustomizationCatalogCategory extends Mage_Shell_Abstract {
    protected $_storeId = 0;

    public function run() {
        $startMessage = date('Y-m-d H:i:s').' START - Inserting EAV Attribute Value include_in_megamenu';
        Mage::log($startMessage);
        echo $startMessage. "\n";

        $this->populateData();

        $finishMessage = date('Y-m-d H:i:s').' FINISH - Inserting EAV Attribute Value include_in_megamenu';
        Mage::log($finishMessage);
        echo $finishMessage. "\n";

        exit;
    }

    protected function populateData() {
        $categoryModel = Mage::getModel('catalog/category');
        $catTree = $categoryModel->getTreeModel()->load();
        $catIds = $catTree->setStoreId($this->_storeId)->getCollection()->getAllIds();
        foreach($catIds as $id){
            $categoryModel->setId($id);
            $categoryModel->setIncludeInMegamenu(1);
            $categoryModel->save();
        }
        return;
    }
}

$shell = new CustomizationCatalogCategory();
$shell->run();