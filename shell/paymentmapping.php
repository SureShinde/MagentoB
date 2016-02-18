<?php
require_once 'abstract.php';

class Bilna_Paymentmapping_Shell extends Mage_Shell_Abstract {
    public function run() {
        $orderCollection = Mage::getModel('paymethod/observer_paymentmapping');
        $process = $orderCollection->process();
        exit;
    }
}

$shell = new Bilna_Paymentmapping_Shell();
$shell->run();
