<?php
/**
 * Description of customizationCatalogCategory
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class CustomizationCatalogCategory extends Mage_Shell_Abstract {
    protected $_includeInMegamenu = 1;

    public function run() {
        $startMessage = date('Y-m-d H:i:s').' START - Inserting EAV Attribute Value include_in_megamenu';
        Mage::log($startMessage);
        echo $startMessage. "\n";

        $this->generate();

        $finishMessage = date('Y-m-d H:i:s').' FINISH - Inserting EAV Attribute Value include_in_megamenu';
        Mage::log($finishMessage);
        echo $finishMessage. "\n";

        exit;
    }

    protected function generate() {
        $categoryModel = Mage::getModel('catalog/category');
        $catTree = $categoryModel->getTreeModel()->load();
        $catIds = $catTree->getCollection()->getAllIds();
        foreach($catIds as $id){
            $categoryModel->setId($id);
            $categoryModel->setIncludeInMegamenu($this->_includeInMegamenu);
            $categoryModel->save();
        }
        return;
    }
}

$shell = new CustomizationCatalogCategory();
$shell->run();