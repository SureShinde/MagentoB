<?php
class RocketWeb_Netsuite_Model_Adminhtml_System_Config_Tax_Customer {
    public function toOptionArray()
    {
        return Mage::getModel('tax/class')
            ->getCollection()
            ->setClassTypeFilter(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)
            ->toOptionArray();
    }
}