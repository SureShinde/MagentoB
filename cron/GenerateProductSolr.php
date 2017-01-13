<?php

/**
 * Description of GenerateProductSolr
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once '../app/Mage.php';
Mage::app();

$model = Mage::helper('bilna_rest/product_generate');
$model->process();
