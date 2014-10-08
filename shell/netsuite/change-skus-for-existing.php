<?php

require_once dirname(__FILE__).'/../abstract.php';

class Rocketweb_Netsuite_Shell_Change_Skus extends Mage_Shell_Abstract {
    public function run() {
        echo "Started changing skus...\n";
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $_products = Mage::getModel('catalog/product')->getCollection();
        echo "Processing {$_products->getSize()} products ...";

        $counter = 0;
        $resumeAt = $this->getArg('resume-at')?(int)$this->getArg('resume-at'):0;
        foreach($_products as $_product) {
            if($counter < $resumeAt) {
                $counter++;
                continue;
            }
            echo '.';
            if($counter%100 == 0) echo $counter."\n";
            try {
                $_product->setSku($_product->getSku() . '-old' );
                $_product->save();
            }
            catch (Exception $e) {
                echo 'Error updating product #' . $_product->getId() . '<br/>';
            }
            $counter++;
        }
    }
}

$shell = new Rocketweb_Netsuite_Shell_Change_Skus();
$shell->run();