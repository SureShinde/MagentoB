<?php
class Bilna_SendReport_Model_Mysql4_Salescategory extends Mage_Core_Model_Mysql4_Abstract{
    protected function _construct()
    {
        $this->_init('sendreport/salescategory', 'send_report_id');
    }   
}