<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	/**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('categorygenerator')->__('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('categorygenerator')->__('General');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
    
    protected function _prepareForm()
    {
        $model = Mage::registry('cat_generator_data');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('generator_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend ' => Mage::helper('categorygenerator')->__('General Information'))
        );

        $fieldset->addField('auto_apply', 'hidden', array(
            'name' => 'auto_apply',
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('categorygenerator')->__('Name'),
            'title' => Mage::helper('categorygenerator')->__('Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('categorygenerator')->__('Description'),
            'title' => Mage::helper('categorygenerator')->__('Description'),
            'style' => 'height: 100px;',
        ));

        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('categorygenerator')->__('Status'),
            'title' => Mage::helper('categorygenerator')->__('Status'),
            'name' => 'is_active',
            'required' => true,
            'options' => array(
                '1' => Mage::helper('categorygenerator')->__('Active'),
                '0' => Mage::helper('categorygenerator')->__('Inactive'),
            ),
        ));

        $form->setValues($model->getData());

        $this->setForm($form);

        Mage::dispatchEvent('adminhtml_promo_catalog_edit_tab_main_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }

}