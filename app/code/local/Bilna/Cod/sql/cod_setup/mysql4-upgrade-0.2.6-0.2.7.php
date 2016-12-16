<?php

$installer = $this;
$installer->startSetup();

// Add transferbri into payment base shipping
try {
    $installer->run("
        UPDATE payment_base_shipping SET exclude_payment = concat(exclude_payment,', transferbri') WHERE delivery = 'no_prepayment';
    ");
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();
