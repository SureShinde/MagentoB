<?php
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Invoicenetsuitetype {
    const TYPE_CASH_SALE = 'CashSale';
    const TYPE_INVOICE = 'Invoice';

    public function toOptionArray()
    {
        return array(
            array('value'=>self::TYPE_CASH_SALE,'label'=>'Cash Sale'),
            array('value'=>self::TYPE_INVOICE,'label'=>'Invoice')
        );
    }
}