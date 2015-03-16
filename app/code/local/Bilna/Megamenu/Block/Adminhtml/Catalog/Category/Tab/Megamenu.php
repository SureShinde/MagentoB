<?php

class Bilna_Megamenu_Block_Adminhtml_Catalog_Category_Tab_Megamenu extends Mage_Adminhtml_Block_Catalog_Form {
    protected $_category;

    public function __construct() {
        parent::__construct();
        
        $this->setShowGlobalIcon(true);
        $this->setTemplate('catalog/category/featured_product_select.phtml');
    }

    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = Mage::registry('category');
        }
        return $this->_category;
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('featuredproduct');
        $form->setDataObject($this->getCategory());

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('catalog')->__('Category Products')));

        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_category', 'featuredproduct');
        $this->_setFieldset(array($attribute), $fieldset);

        $form->addValues($this->getCategory()->getData());

        $form->setFieldNameSuffix('featuredproduct');
        $form->setUseContainer(true);
        $this->setForm($form);
    }
}