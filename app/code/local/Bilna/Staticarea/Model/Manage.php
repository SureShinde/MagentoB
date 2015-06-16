<?php

class Bilna_Staticarea_Model_Manage extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("staticarea/manage");
    }

    public function getContentCollection() {
        $_collection = Mage::getModel('staticarea/contents')->getCollection();
        $_collection->addContentFilter($this->getData('id') ? $this->getData('id') : -1);
        return $_collection;
    }
}