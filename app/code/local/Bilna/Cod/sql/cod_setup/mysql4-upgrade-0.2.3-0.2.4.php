<?php
$installer = $this;

$installer->startSetup();

$installer->run("
	UPDATE payment_base_shipping SET exclude_payment = 'klikpay, klikbca, veritrans, transferbca, transferbni, transfermandiri, vtdirect, mandiriecash, virtualaccountbca' WHERE delivery = 'no_prepayment'
");

$installer->endSetup();