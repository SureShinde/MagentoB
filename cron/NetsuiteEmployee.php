<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once '../lib/Netsuite/NetSuiteService.php';
//require_once '../app/Mage.php';
//Mage::app();

$netsuiteService = new NetSuiteService();
$netsuiteEmployee = getNetsuiteFormatEmployee();
$request = new UpdateRequest();
$request->record = $netsuiteEmployee;
$response = $netsuiteService->update($request);

echo json_encode($response) . "\n\n";
exit;

function getNetsuiteFormatEmployee() {
    $employee = new Employee();
    $employee->internalId = 936;
    $employee->email = 'dhany@bilna.com';
    $employee->homePhone = '0215580157';
    $employee->mobilePhone = '0816745870';
    //$response = updateRecord($service, $employee);
    
    return $employee;
}
