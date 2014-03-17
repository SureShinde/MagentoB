<?php
/**
 * @package    Bilna Static Area Manager
 **/

class Bilna_Staticarea_Block_Adminhtml_Manage_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form {
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('staticarea_form', array ('legend' => Mage::helper('staticarea')->__('General')));
        $version = substr(Mage::getVersion(), 0, 3);

        $fieldset->addField('area_name', 'text', array (
            'label' => Mage::helper('staticarea')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'area_name'
        ));

        $fieldset->addField('block_id', 'text', array (
            'label' => Mage::helper('staticarea')->__('Block ID'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'block_id'
        ));

        /*if(is_null($_data->getData('status')))
            $_data->setData('status', TRUE);*/

        $fieldset->addField('status_area', 'select', array(
            'name' => 'status_area',
            'label' => $this->__('Status'),
            'required' => TRUE,
            'values' => Mage::getModel('staticarea/source_status')->toOptionArray()
        ));

        $fieldset->addField('storeview', 'multiselect', array(
            'name'      => 'storeview[]',
            'label'     => $this->__('Store View'),
            'required'  => TRUE,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE),
        ));

        $fieldset->addField('type', 'select', array(
            'name'      => 'type',
            'label'     => $this->__('Type'),
            'required'  => TRUE,
            'values'    => array(
                'single' => 'single',
                'full'   => 'full'
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getStaticareaData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getStaticareaData());
            Mage::getSingleton('adminhtml/session')->setStaticareaData(null);
        }
        else if (Mage::registry('staticarea_data')) {
            $form->setValues(Mage::registry('staticarea_data')->getData());
        }
        
        return parent::_prepareForm();
    }

}
