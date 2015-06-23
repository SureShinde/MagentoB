<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Helper_Data extends Mage_Core_Helper_Abstract {
    const AW_IS_FORM_DATA_KEY = 'awislider_formdata';
    const AW_IS_FORM_DATA_IMAGES_KEY = 'awislider_formdata_images';
    
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

    public function setFormDataImage($data) {
        if(!($data instanceof Varien_Object))
            $data = new Varien_Object($data);
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::AW_IS_FORM_DATA_IMAGES_KEY);
        if(!is_array($_formData)) $_formData = array();
        $_formData[$data->getId() ? $data->getId() : -1] = $data;
        Mage::getSingleton('adminhtml/session')->setData(self::AW_IS_FORM_DATA_IMAGES_KEY, $_formData);
    }

    public function getFormDataImage($id = null) {
        if(!$id) $id = -1;
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::AW_IS_FORM_DATA_IMAGES_KEY);
        return $_formData && isset($_formData[$id]) ? $_formData[$id] : null;
    }
    
    public function getUseDirectLinks() {
        return Mage::getStoreConfig('awislider/general/directurls');
    }
}
