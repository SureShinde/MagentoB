<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE bilna_payment_confirmation ADD COLUMN entity_id INT(13) DEFAULT 0;

SET FOREIGN_KEY_CHECKS=0;
INSERT INTO core_variable SET code = 'cronPaymentConfirmSenderEmail',name='cronPaymentConfirmSenderEmail';
INSERT INTO core_variable_value SET variable_id = LAST_INSERT_ID(),store_id = 0,plain_value='deni.dhian@bilna.com',html_value='deni.dhian@bilna.com';
INSERT INTO core_variable SET code = 'cronPaymentConfirmSenderName',name='cronPaymentConfirmSenderName';
INSERT INTO core_variable_value SET variable_id = LAST_INSERT_ID(),store_id = 0,plain_value='Deni Dhian',html_value='Deni Dhian';
INSERT INTO core_variable SET code = 'cronPaymentConfirmReceiverEmail',name='cronPaymentConfirmReceiverEmail';
INSERT INTO core_variable_value SET variable_id = LAST_INSERT_ID(),store_id = 0,plain_value='dendhi31@yahoo.com',html_value='dendhi31@yahoo.com';
INSERT INTO core_variable SET code = 'cronPaymentConfirmReceiverName',name='cronPaymentConfirmReceiverName';
INSERT INTO core_variable_value SET variable_id = LAST_INSERT_ID(),store_id = 0,plain_value='Deni',html_value='Deni';
SET FOREIGN_KEY_CHECKS=1;

");
$installer->endSetup();

