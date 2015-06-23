<?php
require_once 'abstract.php';

class Bilna_Installmentmapping_Shell extends Mage_Shell_Abstract {
    public function run() {
        $observer = Mage::getModel('paymethod/observer_installmentmapping');
        $process = $observer->process();
        exit;
    }
}

$shell = new Bilna_Installmentmapping_Shell();
$shell->run();
