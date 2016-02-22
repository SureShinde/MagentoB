<?php

class AW_Affiliate_Block_Adminhtml_Category_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
    }

    protected function _prepareForm()
    {
        /* @var $model AW_Affiliate_Model_Campaign */
        //$campaign = Mage::registry('current_campaign');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('general_');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('awaffiliate')->__('General Information'))
        );

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('awaffiliate')->__('Name'),
            'title' => Mage::helper('awaffiliate')->__('Name'),
            'required' => true,
        ));
        //AW_Affiliate_Block_Adminhtml_Category_Edit_Tab_Categoriesgrid
        $categoryGridHtml = Mage::getSingleton('core/layout')
            ->createBlock('awaffiliate/adminhtml_category_edit_tab_categoriesgrid')
            ->toHtml()
        ;
        $fieldset->addField(
            'gridcontainer_categories', 'note',
            array(
                'label' => $this->__('Select Categories'),
                'text' => $categoryGridHtml
            )
        );

        
        //$form->setValues($campaign->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return Mage::helper('awaffiliate')->__('General');
    }


    public function getTabTitle()
    {
        return Mage::helper('awaffiliate')->__('General Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
