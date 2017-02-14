<?php

/**
 * Description of GenerateProductSolr
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once '../app/Mage.php';
Mage::app();

ini_set('memory_limit', '512M');
ini_set('gd.jpeg_ignore_warning', 1);

$productIds = array_slice($argv, 1);
$model = Mage::helper('bilna_rest/product_generate');
$model->process($productIds);
