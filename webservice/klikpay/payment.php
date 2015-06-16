<?php
/**
 * Description of webservice_klikpay_payment.php
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once '../../app/Mage.php';
Mage::app();

//First we load the model
$model = Mage::getModel('paymethod/observer_webservice_klikpay');
 
//Then execute the task
$model->paymentAction();
