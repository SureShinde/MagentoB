<?php
 
// initialize Magento environment
include_once "app/Mage.php";
Mage::app('admin')->setCurrentStore(0);
 
$helper = Mage::helper('urapidflow');

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