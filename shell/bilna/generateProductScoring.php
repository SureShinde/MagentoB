<?php
require_once dirname(__FILE__) . '/../abstract.php';

class Ccp extends Mage_Shell_Abstract {
    
    public function run() {
        echo "CCP is now starting ..\n\n";

        // pull products list as RJM does
        $productModel = Mage::getModel('ccp/ccpmodel');

        echo "Collecting information ..\n Time: ".strftime('%c');
        $product_inventories = $productModel->getProductInventories();
        $product_sales = $productModel->getProductsSales();
        echo " - ".strftime('%c')."\n\n";

        echo "Updating product scores ..\n Time: ".strftime('%c');
        $product_scorings = $productModel->setProductScoringDataTable($product_inventories, $product_sales);
        echo " - ".strftime('%c')."\n\n";

        echo "Updating product positions ..\n Time: ".strftime('%c');
        $product_position = $productModel->setProductPosition();
        echo " - ".strftime('%c')."\n\n";

        echo "CCP is now updated.\n";
    }
}

$shell = new Ccp();
$shell->run();
exit;