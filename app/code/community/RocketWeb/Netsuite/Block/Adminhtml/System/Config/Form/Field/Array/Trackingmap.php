<?php
class RocketWeb_Netsuite_Block_Adminhtml_System_Config_Form_Field_Array_Trackingmap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct()
    {
        $this->addColumn('carrier_type', array(
            'label' => Mage::helper('adminhtml')->__('Carrier'),
            'size'  => 28,
        ));
        $this->addColumn('internal_netsuite_id', array(
            'label' => Mage::helper('adminhtml')->__('Netsuite internal ID'),
            'size'  => 10,
            'style' => 'width:30px;'
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

        if($columnName == 'carrier_type') {
            $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
            $rendered = '<select name="'.$inputName.'">';
            $options = $this->getCarriers();
            foreach($options as $option) {
                $rendered .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
            }
            $rendered .= '</select>';
            return $rendered;
        }
        else {
            return parent::_renderCellTemplate($columnName);
        }
    }

    public function getCarriers() {
        return Mage::getModel('rocketweb_netsuite/adminhtml_system_config_source_trackingtype')->toOptionArray();
    }

}