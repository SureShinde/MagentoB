<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE sales_flat_order_payment ADD COLUMN va_number VARCHAR(20);
");
$installer->endSetup();
