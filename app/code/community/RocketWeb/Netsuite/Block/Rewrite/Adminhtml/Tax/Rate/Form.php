<?php
class RocketWeb_Netsuite_Block_Rewrite_Adminhtml_Tax_Rate_Form extends Mage_Adminhtml_Block_Tax_Rate_Form {
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->getForm();
        $fieldSet = $form->getElement('base_fieldset');

        $fieldSet->addField('tax_city', 'text', array(
            'name'  => 'tax_city',
            'label' => Mage::helper('tax')->__('City')
        ));

        $rateObject = new Varien_Object(Mage::getSingleton('tax/calculation_rate')->getData());
        $rateData = $rateObject->getData();
        if ($rateObject->getZipIsRange()) {
            list($rateData['zip_from'], $rateData['zip_to']) = explode('-', $rateData['tax_postcode']);
        }
        $form->setValues($rateData);
        $this->setForm($form);

        return $this;
    }
}