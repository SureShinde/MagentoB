<?php
/**
 * Adminhtml Wrapping Gift Event "Wrap Types" field
 *
 * @category   Bilna
 * @package    Bilna_Wrappinggiftevent
 * @author     Bilna Development Team <development@bilna.com>
 */

/**
 * Backend for serialized array data
 *
 */
class Mage_CatalogInventory_Model_System_Config_Backend_Minsaleqty extends Mage_Core_Model_Config_Data
{
    /**
     * Process data after load
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = Mage::helper('cataloginventory/minsaleqty')->makeArrayFieldValue($value);
        $this->setValue($value);
    }

    /**
     * Prepare data before save
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $value = Mage::helper('cataloginventory/minsaleqty')->makeStorableArrayFieldValue($value);
        $this->setValue($value);
    }
}
