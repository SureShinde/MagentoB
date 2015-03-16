<?php
/**
 * Description of webservice_klikbca_inquiry.php
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once '../../app/Mage.php';
Mage::app();

//First we load the model
$model = Mage::getModel('paymethod/observer_webservice_klikbca');
 
//Then execute the task
$model->paymentAction();
