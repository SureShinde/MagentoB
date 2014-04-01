<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    INSERT INTO `bin_code` (`code`,`issuer`,`platform`,`name`)
    VALUES ('601715','megavisa','Visa','Bank Mega VISA CASHIER CARD');
");
$installer->endSetup();
