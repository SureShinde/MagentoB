<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator_Edit_Tab_Categories_Categoriesgrid
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
    private $_afpVO = null;

    protected function _beforeToHtml()
    {
        $this->setTemplate('categorygenerator/catalog/categories/tree.phtml');
        return $this;
    }

    public function getProduct()
    {
        if (is_null($this->_afpVO)) {
            $this->_afpVO = new Varien_Object();
        }
        if (!$this->_afpVO->getCategoryIds()) {
            $_data = Mage::getSingleton('adminhtml/session')->getData(Icube_CategoryGenerator_Helper_Data::FORM_DATA_KEY);
            if (!is_object($_data)) {
                $_data = new Varien_Object($_data);
            }
            if ($_data->getCategoryIds()) {
                $this->_afpVO->setCategoryIds(@explode(',', $_data->getCategoryIds()));
            } else {
                $this->_afpVO->setCategoryIds(array());
                $_automationData = $_data->getCategoryData();
                if ($_automationData && isset($_automationData['categories'])) {
                    $this->_afpVO->setCategoryIds(@explode(',', $_automationData['categories']));
                }
            }
        }
        return $this->_afpVO;
    }
}
