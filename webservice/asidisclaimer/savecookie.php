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

$cookie = Mage::getModel('core/cookie');
$status = false;
$message = 'failed';

if (!empty ($name) || !empty ($value)) { 
    if ($cookie->set($name, $value, null, '/')) {
        $status = true;
        $message = '';
    }
}

echo json_encode(array ('status' => $status, 'message' => $message));
exit;