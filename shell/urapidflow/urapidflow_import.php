<?php
 
// initialize Magento environment
include_once "../../app/Mage.php";
Mage::app('admin')->setCurrentStore(0);
 
$helper = Mage::helper('urapidflow');

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
