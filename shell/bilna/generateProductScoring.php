<?php
require_once dirname(__FILE__) . '/../abstract.php';

class Ccp extends Mage_Shell_Abstract {
    
    public function run() {
        // pull products list as RJM does
        $productModel = Mage::getModel('ccp/ccpmodel');

        $productsList = $productModel->getProductInventories();
        $productsList = $productModel->getProductsSales($productsList);
        $productsList = $productModel->setProductScoringDataTable($productsList);

        // now insert to CCP table
        echo $productModel->setProductPosition();
    }    
}

$shell = new Ccp();
$shell->run();
exit;