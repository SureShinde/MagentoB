<?php
/**
 * run every day at ?
 */

require_once '../app/Mage.php'; // load magento API

Mage::app();
$sendEmail = Mage::getModel('whitelistemail/processing');
$sendEmail->cronEmailWhitelist();
