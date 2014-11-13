<?php
 
// initialize Magento environment
include_once "app/Mage.php";
Mage::app('admin')->setCurrentStore(0);
 
$helper = Mage::helper('urapidflow');

//export
if($argv[1]==1){
	// all product home:
	$helper->run(76);
	// image product:
	$helper->run(82);
	// all product baby:
	$helper->run(53);
	// descrription:
	$helper->run(36);
	// master data category:
	$helper->run(83);
	// Export Product Feed Elevenia - Baby & Kids:
	$helper->run(87);
	// Export Product Feed Elevenia - Beauty & Health:
	$helper->run(88);
	// Export Product Feed Elevenia - Home & Garden:
	$helper->run(89);
	// Export Product Feed Elevenia - Fashion:
	$helper->run(90);
}
//import
else if($argv[1]==2){
	// Import Product Status Price - Baby:
	$helper->run(15);
	// Import Product Group Price - Baby:
	$helper->run(35);
	// Import Product Status Price - Home:
	$helper->run(71);
	// Import Category-Product Association - Baby:
	$helper->run(45);
	// Import Category-Product Association - Home:
	$helper->run(74);
	// Import Description Product - Baby:
	$helper->run(37);
}
//prod feed
else if($argv[1]==3){
	// product feed:
	$helper->run(32);
}