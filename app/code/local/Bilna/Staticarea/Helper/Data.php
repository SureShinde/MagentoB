<?php

class Bilna_Staticarea_Helper_Data extends Mage_Core_Helper_Abstract
{
	const BILNA_IS_FORM_DATA_KEY = 'bilnastaticarea_formdata';
    const BILNA_IS_FORM_DATA_IMAGES_KEY = 'bilnastaticarea_formdata_images';
    
    /**
     * Checking version of Magento
     * @param string $version
     * @return bool true when Magento version >= $version, false - otherwise
     */
    public function checkVersion($version) {
        return version_compare(Mage::getVersion(), $version, '>=');
    }
    
    public function isHttps() {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
    }
    
    public function setFormData($data) {
        if(!($data instanceof Varien_Object))
            $data = new Varien_Object($data);
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::AW_IS_FORM_DATA_KEY);
        if(!is_array($_formData)) $_formData = array();
        $_formData[$data->getId() ? $data->getId() : -1] = $data;
        Mage::getSingleton('adminhtml/session')->setData(self::AW_IS_FORM_DATA_KEY, $_formData);
    }
    
    public function getFormData($id = null) {
        if(!$id) $id = -1;
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::AW_IS_FORM_DATA_KEY);
        return $_formData && isset($_formData[$id]) ? $_formData[$id] : null;
    }

    public function setFormDataContent($data) {
        if(!($data instanceof Varien_Object))
            $data = new Varien_Object($data);
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::BILNA_IS_FORM_DATA_IMAGES_KEY);
        if(!is_array($_formData)) $_formData = array();
        $_formData[$data->getId() ? $data->getId() : -1] = $data;
        Mage::getSingleton('adminhtml/session')->setData(self::BILNA_IS_FORM_DATA_IMAGES_KEY, $_formData);
    }

    public function getFormDataContent($id = null) {
        if(!$id) $id = -1;
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::BILNA_IS_FORM_DATA_IMAGES_KEY);
        return $_formData && isset($_formData[$id]) ? $_formData[$id] : null;
    }
    
    public function getUseDirectLinks() {
        return Mage::getStoreConfig('awislider/general/directurls');
    }
}