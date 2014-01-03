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

class AW_Islider_Model_Image extends Mage_Core_Model_Abstract {
    public function _construct() {
        $this->_init('awislider/image');
    }

    public function getUriLocation() {
        if($this->getData('type')) {
            if($this->getData('type') == AW_Islider_Model_Source_Images_Type::FILE) {
                return Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).Mage::helper('awislider/files')->getFolderName().'/'.$this->getData('location');
            } else {
                $uri = $this->getData('location');
                if (!filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                    $uri = Mage::app()->getStore()->getConfig(Mage::helper('awislider')->isHttps() ? Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL : Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL) . DS . $uri;
                }
                return $uri;
            }
        }
        return null;
    }

    public function removeSelfImage() {
        if($this->getData('location') && $this->getData('type') == AW_Islider_Model_Source_Images_Type::FILE) {
            Mage::helper('awislider/files')->removeFile($this->getData('location'));
            Mage::helper('awislider/files')->removeFile('100x100_'.$this->getData('location'));
        }
        return $this;
    }

    public function removePreviewImage() {
        if($this->getData('location')) {
            $fname =  '100x100_'.($this->getData('type') == AW_Islider_Model_Source_Images_Type::FILE ? $this->getData('location') : md5($this->getData('location')).'.'.Mage::helper('awislider/files')->getExtension($this->getData('location')));
            Mage::helper('awislider/files')->removeFile($fname);
        }
        return $this;
    }

    protected function _beforeSave() {
        if(!is_empty_date($this->getData('active_from')))
            $this->setData('active_from', date(AW_Islider_Model_Mysql4_Image::DATE_FORMAT, strtotime($this->getData('active_from'))));
        if(!is_empty_date($this->getData('active_to')))
            $this->setData('active_to', date(AW_Islider_Model_Mysql4_Image::DATE_FORMAT, strtotime($this->getData('active_to'))));
        return parent::_beforeSave();
    }
}
