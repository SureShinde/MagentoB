<?php
/**
 * Description of Bilna_Visa_Block_System_Config_Form_Field_Installment
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Visa_Block_System_Config_Form_Field_Installment extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct() {
        $this->addColumn('id', array (
            'label' => 'ID*',
            'style' => 'width:30px;text-align:center;'
        ));
        $this->addColumn('label', array (
            'label' => Mage::helper('adminhtml')->__('Installment Label'),
            'style' => 'width:120px'
        ));
        $this->addColumn('value', array (
            'label' => Mage::helper('adminhtml')->__('Value'),
            'style' => 'width:60px;text-align:right;'
        ));
        $this->addColumn('tenor', array (
            'label' => 'Tenor',
            'style' => 'width:60px;text-align:right;'
        ));
        $this->addColumn('merchantid', array (
            'label' => 'Merchant ID',
            'style' => 'width:100px;'
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Installment');
        
        parent::__construct();
    }
}
