<?php

$installer = $this;
$installer->startSetup();
$sql = <<<SQL
ALTER TABLE `aw_affiliate_withdrawal_request` CHANGE `status` `status` ENUM( 'pending', 'paid', 'rejected', 'failed', 'approved' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL 
SQL;

try {
    $installer->run($sql);
} catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();