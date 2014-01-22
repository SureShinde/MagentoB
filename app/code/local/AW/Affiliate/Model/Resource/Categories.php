<?php


class AW_Affiliate_Model_Resource_Categories extends Mage_Core_Model_Mysql4_Abstract
{
    const MYSQL_DATE_FORMAT = 'Y-m-d';

    public function _construct()
    {
        $this->_init('awaffiliate/categories', 'id');
    }
}
