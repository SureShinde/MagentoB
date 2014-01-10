<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 * EAV attribute image backend
 */
class Belvg_Brands_Model_Entity_Attribute_Backend_Image extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

    /**
     * Save uploaded images. This needs to be done in the afterSave
     * method so the object's ID is known.
     *
     * @param Belvg_Brands_Model_Brands $object
     * @return Belvg_Brands_Model_Entity_Attribute_Backend_Image
     * @throws Exception
     */
    public function afterSave($object)
    {
        parent::afterSave($object);
        $attr_code = $this->getAttributeCode();
        try {
            $target = Mage::getModel('brands/brands_image')->getEntityPictureDir($object, $attr_code);
            $uploader = new Varien_File_Uploader($attr_code);
            $result = $uploader->setAllowedExtensions(array('png', 'jpg', 'jpeg', 'gif'))->save($target);
            if (!$result['error']) {
                $object->setData($attr_code, $result['file']);
                $object->getResource()->saveAttribute($object, $attr_code);
                $this->_cleanOldFiles($object);
                $this->_resizeImage($object);
            }
        } catch (Exception $e) {
            if ($e->getCode() !== Varien_File_Uploader::TMP_NAME_EMPTY &&
                    $e->getMessage() !== '$_FILES array is empty') {
                Mage::log(array($e->getMessage(), $e->getCode()), Zend_Log::ERR);
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Remove all but the current image for the given obbject.
     *
     * @param Varien_Object $object
     * @return Belvg_Brands_Model_Entity_Attribute_Backend_Image
     */
    protected function _cleanOldFiles(Varien_Object $object)
    {
        return $this;
        if ($object->getId()) {
            $attr_code = $this->getAttributeCode();
            $currentFile = $object->getData($attr_code);
            $dir = Mage::getModel('brands/brands_image')->getEntityPictureDir($object, $attr_code);
            $files = glob($dir . DS . '*');
            if ($files) {
                $pos = strlen($dir) + 1;
                foreach ($files as $file) {
                    if ($currentFile && substr($file, $pos) === $currentFile) {
                        continue;
                    }

                    @unlink($file);
                }
            }
        }

        return $this;
    }

    /**
     * Resize Image
     *
     * @param Varien_Object $object
     * @return Belvg_Brands_Model_Entity_Attribute_Backend_Image
     */
    protected function _resizeImage(Varien_Object $object)
    {
        $currentFile = $object->getData($this->getAttributeCode());
        if ($currentFile) {
            $currentFile = Mage::getModel('brands/brands_image')
                            ->getEntityPictureDir($object, $this->getAttributeCode()) . DS . $currentFile;

            $width = $height = NULL;
            $image = new Varien_Image($currentFile);

            if ($image->getOriginalWidth() > $this->getMaxWidth()) {
                $width = $this->getMaxWidth();
            }

            if ($image->getOriginalHeight() > $this->getMaxHeight()) {
                if (!$width) {
                    $width = $this->getMaxWidth();
                }

                $height = $this->getMaxHeight();
            }

            if ($width) {
                $image->keepAspectRatio(TRUE);
                $image->resize($width, $height);
                $image->save($currentFile);
            }
        }

        return $this;
    }

    /**
     * Get atribute code
     *
     * @return string
     */
    protected function getAttributeCode()
    {
        return $this->getAttribute()->getAttributeCode();
    }

    /**
     * Get maximum image height. Resize any big image to save resources in futere resizes
     *
     * @return int
     */
    protected function getMaxHeight()
    {
        return Mage::helper('brands')->getLogoHeight($this->getAttributeCode());
    }

    /**
     * Get maximum image width. Resize any big image to save resources in futere resizes
     *
     * @return int
     */
    protected function getMaxWidth()
    {
        return Mage::helper('brands')->getLogoWidth($this->getAttributeCode());
    }

}
