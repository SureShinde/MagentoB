<?php
/**
 * Description of Vtdirect_confirmation
 * 
 * Run every 5 minutes
 *
 * @author Bilna Development Team
 */

require_once '../app/Mage.php';
Mage::app();

//First we load the model
$model = Mage::getModel('paymethod/observer_vtdirect');
 
//Then execute the task
$model->confirmationProcess();
