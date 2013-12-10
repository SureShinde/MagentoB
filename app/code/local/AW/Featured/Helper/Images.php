<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Helper_Images extends Mage_Core_Helper_Abstract
{
    public function getGalleryImages($productId)
    {
        $_product = Mage::getModel('catalog/product')->load($productId);
        if ($_product->getData()) {
            $_collection = $_product->getMediaGalleryImages();
            $_collection = Mage::getModel('awfeatured/data_collection')->createFrom($_collection);
            return $_collection;
        }
        return null;
    }

    public static function getGalleryImage($src, $imageId)
    {
        if (!($src instanceof Varien_Data_Collection)) {
            $_pid = $src->getId();
            $_collection = $src->getMediaGalleryImages();
            if (!$_collection) {
                $src = Mage::getModel('catalog/product')->load($_pid);
                $_collection = $src->getMediaGalleryImages();
            }
        } else {
            $_collection = $src;
        }
        foreach ($_collection as $_image) {
            if ($_image->getData('value_id') == $imageId) {
                return $_image;
            }
        }
        return null;
    }

    public function getProductImage($product, $imageId = null, $width = 120, $height = 120, $type = 'small_image')
    {
        if (!is_object($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        if ($product->getData()) {
            if (!$imageId && $product->getData($type) && $product->getData($type) != 'no_selection') {
                return Mage::helper('catalog/image')->init($product, $type)->resize($width, $height);
            }
            if ($imageId && ($_gImage = self::getGalleryImage($product, $imageId))) {
                $_imageUrl = Mage_Core_Model_Store::URL_TYPE_MEDIA.DS . self::getFolderName()
                    . DS . self::imageResizeRemote($_gImage->getData('url'), $imageId, $width, $height);
                if ($_imageUrl) {
                    $baseUrlXmlPath = Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL;
                    if (Mage::helper('awfeatured')->isHttps()) {
                        $baseUrlXmlPath = Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL;
                    }
                    return Mage::app()->getStore()->getConfig($baseUrlXmlPath) . $_imageUrl;
                }
            }
            return Mage::helper('catalog/image')->init($product, $type)->resize($width, $height);
        }
        return null;
    }

    public static function imageResize($image, $width = 100, $height = 100)
    {
        $newName = $width.'x'.$height.'_'.$image;
        $basePath = self::getRealImageFolderPath();
        if (file_exists($basePath.$newName)) {
            return $newName;
        }
        if (class_exists('Varien_Image_Adapter_Gd2')) {
            try {
                $_image = new Varien_Image_Adapter_Gd2();
                $_image->open($basePath.$image);
                $_image->keepAspectRatio(true);
                $_image->resize($width, $height);
                if (Mage::helper('awfeatured')->checkVersion('1.4')) {
                    $_image->save($basePath, $newName);
                } else {
                    $_image->save(null, $newName);
                }
                return $newName;
            } catch(Exception $ex) {
                Mage::logException($ex);
                return false;
            }
        }
        return false;
    }

    public function removeFile($file)
    {
        return @unlink(self::getRealImageFolderPath().$file);
    }

    public static function imageResizeRemote($image, $newName = null, $width = 100, $height = 100)
    {
        if (!$newName) {
            $newName = md5($image) . '.' . self::getExtension($image);
        }
        if (!self::getExtension($newName)) {
            $newName .= '.'.self::getExtension($image);
        }
        if (file_exists(self::getRealImageFolderPath() . $width . 'x' . $height . '_' . $newName)) {
            return $width . 'x' . $height . '_' . $newName;
        }
        if (self::curlDownload($image, self::getRealImageFolderPath() . $newName)) {
            $_result = self::imageResize($newName, $width, $height);
            @unlink(self::getRealImageFolderPath() . $newName);
            return $_result;
        }
        return false;
    }

    /**
     * Returns file extension
     * @param string $fname
     * @return string
     */
    public static function getExtension($fname)
    {
        $_pi = pathinfo($fname);
        return isset($_pi['extension']) ? $_pi['extension'] : false;
    }

    public static function curlDownload($from, $dest)
    {
        if (!self::curlCheckFunctions()) {
            return false;
        }
        $_ch = curl_init();
        if (!$_ch) {
            return false;
        }
        $df = fopen($dest, "w");
        if (!$df) {
            return false;
        }
        if (!curl_setopt($_ch, CURLOPT_URL, $from)) {
            fclose($df);
            curl_close($_ch);
            return false;
        }
        if (curl_setopt($_ch, CURLOPT_FILE, $df)
                && curl_setopt($_ch, CURLOPT_HEADER, 0)
                && curl_exec($_ch)) {
            curl_close($_ch);
            fclose($df);
            return true;
        }
        return false;
    }

    protected static function curlCheckFunctions()
    {
        return function_exists("curl_init") &&
                function_exists("curl_setopt") &&
                function_exists("curl_exec") &&
                function_exists("curl_close")
        ;
    }

    public function removeThumbnails()
    {
        foreach (glob(self::getRealImageFolderPath().'*.*') as $thumb) {
            @unlink($thumb);
        }
    }

    public static function getPath()
    {
        return Mage::getBaseDir('media') . DS . self::getFolderName() . DS;
    }

    public static function getRealImageFolderPath()
    {
        return BP . DS . Mage_Core_Model_Store::URL_TYPE_MEDIA . DS . self::getFolderName() . DS;
    }

    public static function getFolderName()
    {
        return 'aw_fp3';
    }
}
