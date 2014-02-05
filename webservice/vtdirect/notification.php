<?php
/**
 * Description of webservice_vtdirect_notification.php
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once '../../app/Mage.php';
Mage::app();

//First we load the model
$model = Mage::getModel('paymethod/observer_webservice_vtdirect');
 
//Then execute the task
$model->notificationAction();
