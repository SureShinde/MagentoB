<?php

require_once '../app/Mage.php';
Mage::app();

$model = Mage::getModel('sitemap/observer');
$model->scheduledGenerateSitemaps();
