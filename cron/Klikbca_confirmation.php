<?php
/**
 * Description of Klikbca_confirmation
 * 
 * Run every 2 minutes
 *
 * @author Bilna Development Team
 */

require_once '../app/Mage.php';
Mage::app();

//First we load the model
$model = Mage::getModel('paymethod/observer_klikbca');
 
//Then execute the task
$model->confirmationProcess();

//Mage::getStoreConfig(sprintf("payment/klikbca/%s_lock_path", $status))
