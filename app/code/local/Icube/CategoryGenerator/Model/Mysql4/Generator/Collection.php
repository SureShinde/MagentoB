<?php

class Icube_CategoryGenerator_Model_Mysql4_Generator_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('categorygenerator/generator');
    }
}