<?php

class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit_Tab_Json extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('bilna_pricevalidation');
        $source = Mage::getSingleton('bilna_pricevalidation/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('json_form', array('legend'=>$this->__('Profile Configuration')));

        if (!$profile || !$profile->getId()) {
            $fieldset->addField('json_import', 'textarea', array(
                'label'     => $this->__('Import Profile Configuration'),
                'name'      => 'json_import',
                'style'     => 'width:500px; height:500px; font-family:Courier New;',
            ));
        }/* else {
            $fieldset->addField('json_export', 'textarea', array(
                'label'     => $this->__('Export Profile Configuration'),
                'name'      => 'json_export',
                'readonly'  => true,
                'value'     => $profile->exportToJSON(),
                'style'     => 'width:500px; height:500px; font-family:Courier New;',
            ));
        }*/

        return parent::_prepareForm();
    }

    public function indent($json) {


    }
}
