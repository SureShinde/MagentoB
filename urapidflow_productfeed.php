<?php
 
// initialize Magento environment
include_once "app/Mage.php";
Mage::app('admin')->setCurrentStore(0);
 
$helper = Mage::helper('urapidflow');
 
// product feed:
$helper->run(32);