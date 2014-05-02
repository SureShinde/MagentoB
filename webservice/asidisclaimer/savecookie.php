<?php
/**
 * Description of webservice_asidisclaimer_savecookie.php
 *
 * @author Bilna Development Team <development@bilna.com>
 */
ini_set('display_errors', 1);
require_once '../../app/Mage.php';
Mage::app();

//get parameter
$name = $_POST['cookie_name'];
$value = $_POST['cookie_value'];

//load the model
$model = Mage::getModel('catalog/webservice_asidisclaimer');

if ($model->saveCookieAction($name, $value)) {
    $status = true;
    $message = '';
}
else {
    $status = false;
    $message = 'failed';
}

echo json_encode(array ('status' => $status, 'message' => $message));
exit;