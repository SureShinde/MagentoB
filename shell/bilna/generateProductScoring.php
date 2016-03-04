<?php
require_once dirname(__FILE__) . '/../abstract.php';

class Ccp extends Mage_Shell_Abstract {
    
    public function run() {
        Mage::log("CCP is now starting ..");    // recording start time

        // pull products list as RJM does
        $productModel = Mage::getModel('ccp/ccpmodel');

        $product_stock = $productModel->getProductInventories();
        $product_sales = $productModel->getProductsSales();

        $product_scoring = $productModel->setProductScoringDataTable($product_stock, $product_sales);

        // now insert to CCP table
        $product_position = $productModel->setProductPosition();

        Mage::log("CCP is now finished.");    // recording finish time
    }    
}

$shell = new Ccp();
$shell->run();
exit;