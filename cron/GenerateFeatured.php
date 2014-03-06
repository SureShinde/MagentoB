<?php
/**
 * Description of GenerateFeatured
 * Run everyday
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once '../app/Mage.php';
Mage::app();

//First we load the model
$model = Mage::getModel('awfeatured/observer_generatefeatured');
 
//Then execute the task
$model->process();
