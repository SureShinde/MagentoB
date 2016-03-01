<?php
/**
 * run every day at ?
 */

require_once realpath(dirname(__FILE__)).'/../app/Mage.php'; // load magento API

Mage::app();
$sendMail = Mage::getModel('Paymentconfirmation/payment');
$sendMail->cronEmailPaymentconfirmation();
//$sendEmail = Mage::getModel('whitelistemail/processing');
//$sendEmail->cronEmailWhitelist();

