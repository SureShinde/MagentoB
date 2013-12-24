<?php
/**
 * @author      Ferdian Robianto < robianto.ferdian@gmail.com >
 * @category    Icube
 * @package     Megamenu
 * @copyright   Copyright (c) 2013 robianto.ferdian@gmail.com
 */
class Guidance_Megamenu_Block_Catalog_Category_Tab_Staticblock extends Mage_Adminhtml_Block_Catalog_Form
{
    /**
     * Initialize tab
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes
     */
    protected function _prepareForm() {
        parent::_prepareLayout();
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('catalog')->__('Static Block')));

        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_category', 'staticblock');
        $this->_setFieldset(array($attribute), $fieldset);

        $form->addValues('test');

        $form->setFieldNameSuffix('staticblock');
        $form->setUseContainer(true);
        $this->setForm($form);
    }

}