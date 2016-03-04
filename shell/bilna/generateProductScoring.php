<?php
require_once dirname(__FILE__) . '/../abstract.php';

class Ccp extends Mage_Shell_Abstract {
    
    public function run() {
        echo "CCP is now starting ..\n";

        // pull products list as RJM does
        $productModel = Mage::getModel('ccp/ccpmodel');

        echo "Collecting information ..\n";
        $product_stock = $productModel->getProductInventories();
        $product_sales = $productModel->getProductsSales();

        echo "Updating product scores ..\n";
        $product_scoring = $productModel->setProductScoringDataTable($product_stock, $product_sales);

        echo "Updating product positions ..\n";
        $product_position = $productModel->setProductPosition();

        echo "CCP is now updated.\n";
    }    
}

$shell = new Ccp();
$shell->run();
exit;