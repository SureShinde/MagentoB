<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('categorygenerator')->__('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('categorygenerator')->__('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
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

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
                ->setTemplate('promo/fieldset.phtml')
                ->setNewChildUrl($this->getUrl('*/*/newConditionHtml/form/generator_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
                    'legend' => Mage::helper('categorygenerator')->__('Conditions (leave blank for all products)'))
                )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('categorygenerator')->__('Conditions'),
            'title' => Mage::helper('categorygenerator')->__('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));


        $form->setValues($model->getData());

        //$form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
