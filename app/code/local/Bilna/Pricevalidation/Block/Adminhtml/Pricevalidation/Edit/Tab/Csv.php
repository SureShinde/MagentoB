<?php

class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit_Tab_Csv extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('bilna_pricevalidation');
        $source = Mage::getSingleton('bilna_pricevalidation/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('csv_form', array('legend'=>$this->__('CSV Options')));

        $encodings = $source->setPath('encoding')->toOptionArray();
        if ($profile->getProfileType()=='import') {
            $fieldset->addField('encoding_from', 'select', array(
                'label'     => $this->__('File Encoding'),
                'name'      => 'options[encoding][from]',
                'value'     => $profile->getData('options/encoding/from'),
                'values'    => $encodings,
            ));
        } else {
            unset($encodings['auto']);
            $fieldset->addField('encoding_to', 'select', array(
                'label'     => $this->__('File Encoding'),
                'name'      => 'options[encoding][to]',
                'value'     => $profile->getData('options/encoding/to'),
                'values'    => $encodings,
            ));
        }

        $fieldset->addField('encoding_illegal_char', 'select', array(
            'label'     => $this->__('Action to take on illegal character during conversion'),
            'name'      => 'options[encoding][illegal_char]',
            'values'    => $source->setPath('encoding_illegal_char')->toOptionArray(),
            'value'     => $profile->getData('options/encoding/illegal_char'),
        ));

        return parent::_prepareForm();
    }
}
