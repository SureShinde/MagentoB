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
 * Brand image model
 */
class Belvg_Brands_Model_Brands_Image extends Mage_Core_Model_Abstract
{

    /**
     * Cache key for saving image dir in object
     *
     * @var string
     */
    protected $_brandsImageDir = '_brands_image_dir_cache';

    /**
     * Cache key for saving image url in object
     *
     * @var string
     */
    protected $_brandsImageUrl = '_brands_image_url_cache';

    /**
     * image processor
     *
     * @var Varien_Image
     */
    protected $_processor;

    /**
     * New image width
     *
     * @var int
     */
    protected $_width;

    /**
     * New image height
     *
     * @var int
     */
    protected $_height;

    /**
     * Image base file link
     *
     * @var string
     */
    protected $_baseFile;

    /**
     * Image base file placeholder link
     *
     * @var string
     */
    protected $_isBaseFilePlaceholder;

    /**
     * Image resized file link
     *
     * @var string
     */
    protected $_newFile;

    /**
     * Image backround
     *
     * @var array
     */
    protected $_backgroundColor = array(255, 255, 255);

    /**
     * Resize quality
     */

    const BRANDS_IMAGE_QUALITY = 100;

    public function getEntityPictureDir(Belvg_Brands_Model_Brands $object, $type)
    {
        $dir = $object->getData($this->_brandsImageDir . $type);

        if (is_null($dir) && $object->getId()) {
            $dir = Mage::getBaseDir('media') . DS;
            $dir .= $object->getResource()->getType() . DS;
            $dir .= $type . DS;
            $dir .= $object->getId();
            $object->setData($this->_brandsImageDir . $type, $dir);
        }

        return $dir;
    }

    /**
     * Set processor
     *
     * @return Belvg_Brands_Model_Brands_Image
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * Set params o the image processor
     *
     * @return Varien_Image
     */
    public function getImageProcessor()
    {
        if (!$this->_processor) {
            $this->_processor = new Varien_Image($this->getBaseFile());
        }

        $this->_processor->keepAspectRatio(TRUE);
        $this->_processor->keepFrame(TRUE);
        $this->_processor->keepTransparency(TRUE);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality(self::BRANDS_IMAGE_QUALITY);
        return $this->_processor;
    }

    /**
     * @see Varien_Image_Adapter_Abstract
     * @return Belvg_Brands_Model_Brands_Image
     */
    public function resize()
    {
        if (is_null($this->getWidth()) && is_null($this->getHeight())) {
            return $this;
        }

        $this->getImageProcessor()->resize($this->_width, $this->_height);
        return $this;
    }

    /**
     * Get image base file address
     *
     * @return string
     */
    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    /**
     * Set image width
     *
     * @return Belvg_Brands_Model_Brands_Image
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    /**
     * Get image width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Set image height
     *
     * @return Belvg_Brands_Model_Brands_Image
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    /**
     * Get image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Check that image was cached
     *
     * @return boolean
     */
    public function isCached()
    {
        return $this->_fileExists($this->_newFile);
    }

    /**
     * First check this file on FS
     * If it doesn't exist - try to download it from DB
     *
     * @param string $filename
     * @return bool
     */
    protected function _fileExists($filename)
    {
        if (file_exists($filename)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Remove cached images
     *
     * @return void
     */
    public function clearCache()
    {
        $directory = Mage::getBaseDir('media') . DS . 'brands' . DS . 'cache' . DS;
        $io_file = new Varien_Io_File();
        $io_file->rmdir($directory, TRUE);
    }

    /**
     * Get resized image path
     *
     * @return string
     */
    public function getNewFile()
    {
        return $this->_newFile;
    }

    /**
     * Set image background color
     *
     * @param array $rgbArray
     * @return Belvg_Brands_Model_Brands_Image
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->_backgroundColor = $rgbArray;
        return $this;
    }

    /**
     * Save resized image     *
     * @return Belvg_Brands_Model_Brands_Image
     */
    public function saveFile()
    {
        $filename = $this->getNewFile();
        $this->getImageProcessor()->save($filename);
        return $this;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getUrl()
    {
        $baseDir = Mage::getBaseDir('media');
        $path = str_replace($baseDir . DS, "", $this->_newFile);
        return Mage::getBaseUrl('media') . str_replace(DS, '/', $path);
    }

    /**
     * Set filenames for base file and new file
     *
     * @param Belvg_Brands_Model_Brands $object
     * @return Belvg_Brands_Model_Brands_Image
     */
    public function setBaseFile(Belvg_Brands_Model_Brands $object)
    {
        $this->_isBaseFilePlaceholder = FALSE;

        $file = $object->getData('image');

        if (($file) && (0 !== strpos($file, DS, 0))) {
            $file = DS . $file;
        }

        $baseDir = $this->getEntityPictureDir($object, 'image');

        if ($file) {
            if ((!$this->_fileExists($baseDir . $file))) {
                $file = NULL;
            }
        }

        if (!$file) {
            // replace file with skin or default skin placeholder
            $skinBaseDir = Mage::getDesign()->getSkinBaseDir();

            if (Mage::app()->getStore()->isAdmin()) {
                $_package = 'default';
                $skinPlaceholder = '/images/placeholder/thumbnail.jpg';
            } else {
                $_package = 'base';
                $skinPlaceholder = '/images/catalog/product/placeholder/image.jpg';
            }

            $file = $skinPlaceholder;
            if (file_exists($skinBaseDir . $file)) {
                $baseDir = $skinBaseDir;
            } else {
                $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default'));
                if (!file_exists($baseDir . $file)) {
                    $_package = Mage::app()->getStore()->isAdmin() ? 'default' : 'base';
                    $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default', '_package' => $_package));
                }
            }

            $this->_isBaseFilePlaceholder = TRUE;
        }

        $baseFile = $baseDir . $file;

        if ((!$file) || (!file_exists($baseFile))) {
            throw new Exception(Mage::helper('catalog')->__('Image file was not found.'));
        }

        $this->_baseFile = $baseFile;

        // build new filename (most important params)
        $path = array(
                Mage::getBaseDir('media'),
                'brands',
                'cache',
                Mage::app()->getStore()->getId(),
                $path[] = $object->getId()
        );

        if ((!empty($this->_width)) || (!empty($this->_height))) {
            $path[] = "{$this->_width}x{$this->_height}";
        }

        // add misk params as a hash
        $miscParams = array(
                ($this->_keepAspectRatio ? '' : 'non') . 'proportional',
                'quality' . $this->_quality,
                'id' . $object->getId()
        );

        $path[] = md5(implode('_', $miscParams));

        // append prepared filename
        $this->_newFile = implode('/', $path) . $file; // the $file contains heading slash

        return $this;
    }

}