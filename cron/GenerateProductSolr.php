<?php

/**
 * Description of GenerateProductSolr
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once '../app/Mage.php';
Mage::app();

ini_set('memory_limit', '512M');

$model = Mage::helper('bilna_rest/product_generate');
$model->process();
