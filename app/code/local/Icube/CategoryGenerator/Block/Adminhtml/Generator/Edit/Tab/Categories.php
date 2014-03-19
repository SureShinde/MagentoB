<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator_Edit_Tab_Categories extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

	public function getTabLabel()
    {
        return Mage::helper('categorygenerator')->__('Categories');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('categorygenerator')->__('Categories');
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
        
        $fieldset = $form->addFieldset('categories', array('legend ' => Mage::helper('categorygenerator')->__('Categories')));
        
        $categoryGridHtml = Mage::getSingleton('core/layout')
            ->createBlock('categorygenerator/adminhtml_generator_edit_tab_categories_categoriesgrid')
            ->toHtml();
            
        $fieldset->addField(
            'gridcontainer_categories', 'note',
            array(
                'label' => $this->__('Select Categories'),
                'text' => $categoryGridHtml
        ));
        
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}