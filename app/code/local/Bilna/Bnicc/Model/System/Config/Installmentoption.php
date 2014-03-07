<?php
/**
 * Description of Bilna_Bnicc_Model_System_Config_Installmentoption
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Bnicc_Model_System_Config_Installmentoption {
    public function toOptionArray() {
        return array (
            array ('value' => 1, 'label' => Mage::helper('adminhtml')->__('Item')),
            array ('value' => 2, 'label' => Mage::helper('adminhtml')->__('Order'))
        );
    }
    
    public function toArray() {
        return array (
            1 => Mage::helper('adminhtml')->__('Item'),
            2 => Mage::helper('adminhtml')->__('Order'),
        );
    }
}
