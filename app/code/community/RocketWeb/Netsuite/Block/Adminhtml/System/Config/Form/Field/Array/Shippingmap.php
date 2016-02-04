<?php
class RocketWeb_Netsuite_Block_Adminhtml_System_Config_Form_Field_Array_Shippingmap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct()
    {
        $this->addColumn('shipping_method', array(
            'label' => Mage::helper('adminhtml')->__('Shipping Method'),
            'size'  => 28,
        ));
        $this->addColumn('shipping_description', array(
            'label' => Mage::helper('adminhtml')->__('Shipping Description'),
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

        if($columnName == 'shipping_method') {
            $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
            $rendered = '<select name="'.$inputName.'">';
            $options = $this->getAllShippingMethods();
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

    public function getAllShippingMethods() {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $options = array();

        foreach($methods as $_ccode => $_carrier) {
            if($_methods = $_carrier->getAllowedMethods()) {
                if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title")) $_title = $_ccode;
                foreach($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $options[] = array('value' => $_code, 'label' => $_title.' - '.$_method);
                }
            }
        }

        return $options;
    }

}