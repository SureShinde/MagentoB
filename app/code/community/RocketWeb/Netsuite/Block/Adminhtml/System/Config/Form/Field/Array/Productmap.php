<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Block_Adminhtml_System_Config_Form_Field_Array_Productmap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {

    protected $_product_attributes_codes = null;

    public function __construct()
    {
        $this->addColumn('netsuite', array(
            'label' => Mage::helper('adminhtml')->__('Net Suite Field'),
            'size'  => 28,
        ));
        $this->addColumn('netsuite_settings', array(
            'label' => Mage::helper('adminhtml')->__('Net Suite Settings'),
            'size'  => 28,
        ));
        $this->addColumn('magento', array(
            'label' => Mage::helper('adminhtml')->__('Magento'),
            'size'  => 10,
            'style' => 'width:30px;'

        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add new mapping');

        parent::__construct();
        $this->setTemplate('rocketweb_netsuite/system/config/form/field/productmap.phtml');
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }

        if($columnName == 'netsuite') {
            return $this->_renderNetsuiteCellTemplate($columnName);
        }
        if($columnName == 'netsuite_settings') {
            return $this->_renderNetsuiteSettingsCellTemplate($columnName);
        }
        if($columnName == 'magento') {
            return $this->_renderMagentoCellTemplate($columnName);
        }

    }

    protected function _renderNetsuiteCellTemplate($columnName) {
        return parent::_renderCellTemplate($columnName);
    }

    protected function _renderNetsuiteSettingsCellTemplate($columnName) {
        $inputName  = $this->getElement()->getName() . '[#{_id}][netsuite_field_type]';
        $rendered = '<div class="productmap_producttype_select_container"><select name="'.$inputName.'" id="'.$inputName.'" onchange="rw_productmap_change_type(this)">';
        $options = $this->getNetsuiteFieldTypes();
        foreach($options as $option) {
            $rendered .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
        }
        $rendered .= '</select></div>';
        $rendered.='<div style="display:none" class="netsuite_field_list_id"><br/>Net Suite List id: '.$this->_renderTextField('netsuite_list_id').'</div>';
        $rendered.='<div style="display:none" class="netsuite_field_value"><br/>Value: '.$this->_renderTextField('netsuite_field_value').'</div>';
        $rendered.='<div style="display:none" class="netsuite_field_search_class_name"><br/>Search class name: '.$this->_renderTextField('netsuite_field_search_class_name').'</div>';
        $rendered.='<div style="display:none" class="netsuite_field_name_field"><br/>Name Field: '.$this->_renderTextField('netsuite_field_name_field').'</div>';
        return $rendered;
    }

    protected function _renderMagentoCellTemplate($columnName) {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        $rendered = '<select name="'.$inputName.'">';
        $options = $this->getProductAttributes();
        foreach($options as $option) {
            $rendered .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
        }
        $rendered .= '</select>';
        return $rendered;
    }

    public function getProductAttributes() {
        if (is_null($this->_product_attributes_codes)) {
            $this->_product_attributes_codes = array();

            $this->_product_attributes_codes[] = array('value'=>'','label' => 'None');

            $config = Mage::getModel('eav/config');
            $attributes_codes = $config->getEntityAttributeCodes('catalog_product');
            foreach($attributes_codes as $attribute_code) {
                $attribute = $config->getAttribute('catalog_product', $attribute_code);
                if ($attribute !== false && $attribute->getAttributeId() > 0) {
                    $this->_product_attributes_codes[] = array('value' => $attribute->getAttributeCode(),
                                                                'label'=> addslashes($attribute->getFrontend()->getLabel().' ('.$attribute->getAttributeCode().')'));
                }
            }
            asort($this->_product_attributes_codes);
        }
        return $this->_product_attributes_codes;
    }

    protected function getNetsuiteFieldTypes() {
        return array(
            array('value' => RocketWeb_Netsuite_Model_Product_Map_Value::FIELD_TYPE_STANDARD,'label'=>'Standard Field'),
            array('value' => RocketWeb_Netsuite_Model_Product_Map_Value::FIELD_TYPE_RECORD,'label'=>'Record Field'),
            array('value' => RocketWeb_Netsuite_Model_Product_Map_Value::FIELD_TYPE_CUSTOM_SIMPLE,'label'=>'Custom Filed - simple'),
            array('value' => RocketWeb_Netsuite_Model_Product_Map_Value::FIELD_TYPE_CUSTOM_LIST,'label'=>'Custom Field - list'),
            array('value' => RocketWeb_Netsuite_Model_Product_Map_Value::FIELD_TYPE_CUSTOM_CHECKBOX,'label'=>'Custom Field - checkbox'),
        );
    }


    protected function _renderTextField($columnName)
    {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';


        return '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' . '/>';
    }

}