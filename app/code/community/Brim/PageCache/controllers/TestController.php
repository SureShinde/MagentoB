<?php
/**
 * Created by JetBrains PhpStorm.
 * User: brian
 * Date: 10/28/12
 * Time: 4:26 PM
 */
 
class Brim_PageCache_TestController extends Mage_Core_Controller_Front_Action {

    public function jsonAction() {

        $rootBlock = Mage::helper('brim_pagecache')->enablePageCache();

        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody('[{id: 1}, {id:100}]');
    }
}