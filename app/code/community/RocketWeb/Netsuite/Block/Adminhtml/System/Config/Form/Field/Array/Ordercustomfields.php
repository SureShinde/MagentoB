<?php
class RocketWeb_Netsuite_Block_Adminhtml_System_Config_Form_Field_Array_Ordercustomfields extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct()
    {
        $this->addColumn('netsuite_field_name', array(
            'label' => Mage::helper('adminhtml')->__('Net Suite field name'),
            'size'  => 28,
        ));
        $this->addColumn('netsuite_field_type',array(
            'label' => Mage::helper('adminhtml')->__('Net Suite field type'),
            'size'  => 28,
        ));
        $this->addColumn('netsuite_list_internal_id',array(
            'label' => Mage::helper('adminhtml')->__('Net Suite list internal id (list type only)'),
            'size'  => 28,
        ));
        $this->addColumn('value_type', array(
            'label' => Mage::helper('adminhtml')->__('Value Type'),
            'size'  => 28,
        ));
        $this->addColumn('value', array(
            'label' => Mage::helper('adminhtml')->__('Value'),
            'size'  => 28,
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add new mapping');

        parent::__construct();
        $this->setTemplate('rocketweb_netsuite/system/config/form/field/array_dropdown.phtml');
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }

        if($columnName == 'value_type') {
            $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
            $rendered = '<select name="'.$inputName.'">';
            $rendered .= '<option value="fixed">Fixed Value</option>';
            $rendered .= '<option value="order_attribute">Magento Order Attribute</option>';
            $rendered .= '</select>';
            return $rendered;
        }
        else if($columnName == 'netsuite_field_type') {
            $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
            $rendered = '<select name="'.$inputName.'">';
            $rendered .= '<option value="simple">Custom field - simple (string, number etc)</option>';
            $rendered .= '<option value="list">Custom field - List</option>';
            $rendered .= '<option value="standard">Standard field</option>';
            $rendered .= '</select>';
            return $rendered;
        }
        else {
            return parent::_renderCellTemplate($columnName);
        }
    }
}