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

class AW_Islider_Helper_Files extends Mage_Core_Helper_Abstract {
    /**
    * Returns media folder name
    * @return string
    */
    public static function getFolderName() {
        return 'aw_islider';
    }

    /**
    * Returns full path to uploads storage
    * @return string
    */
    public static function getPath() {
        return Mage::getBaseDir('media').DS.self::getFolderName().DS;
    }

    public static function getRealImageFolderPath() {
        return BP.DS.Mage_Core_Model_Store::URL_TYPE_MEDIA.DS.Mage::helper('awislider/files')->getFolderName().DS;
    }

    public static function imageResize($image, $width = 100, $height = 100) {
        $newName = $width.'x'.$height.'_'.$image;
        $basePath = self::getRealImageFolderPath();
        if(file_exists($basePath.$newName)) return $newName;
        if(class_exists('Varien_Image_Adapter_Gd2')) {
            try {
                $_image = new Varien_Image_Adapter_Gd2();
                $_image->open($basePath.$image);
                $_image->keepAspectRatio(true);
                $_image->resize($width, $height);
                if(Mage::helper('awislider')->checkVersion('1.4')) {
                    $_image->save($basePath, $newName);
                } else {
                    $_image->save(null, $newName);
                }
                return $newName;
            } catch(Exception $ex) {
                return false;
            }
        }
        return false;
    }

    public function removeFile($file) {
        return @unlink(self::getRealImageFolderPath().$file);
    }

    public function imageResizeRemote($image, $width = 100, $height = 100) {
        $newName = md5($image).'.'.self::getExtension($image);
        if(self::curlDownload($image, self::getRealImageFolderPath().$newName)) {
            $_result = self::imageResize($newName, $width, $height);
            @unlink(self::getRealImageFolderPath().$newName);
            return $_result;
        }
        return false;
    }

    /**
    * Returns file extension
    * @param string $fname
    * @return string
    */
    public static function getExtension($fname) {
        $_pi = pathinfo($fname);
        return isset($_pi['extension']) ? $_pi['extension'] : '';
    }

    public function isAllowedImage($type) {
        $_allowedMimeTypes = array(
            'image/jpeg',
            'image/png',
            'image/gif'
        );
        return (in_array($type, $_allowedMimeTypes));
    }

    public static function curlDownload($from, $dest) {
        if(!self::curlCheckFunctions()) return false;
        $_ch = curl_init();
        curl_setopt($_ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($_ch, CURLOPT_SSL_VERIFYHOST, 0);
        if(!$_ch) return false;
        $df = fopen($dest, "w");
        if(!$df) return false;
        if(!curl_setopt($_ch, CURLOPT_URL, $from)) {
            fclose($df);
            curl_close($_ch);
            return false;
        }
        if(curl_setopt($_ch, CURLOPT_FILE, $df)
            && curl_setopt($_ch, CURLOPT_HEADER, 0)
            && curl_exec($_ch)) {
            curl_close($_ch);
            fclose($df);
            return true;
        }
        return false;
    }

    protected static function curlCheckFunctions() {
        return function_exists("curl_init") &&
        function_exists("curl_setopt") &&
        function_exists("curl_exec") &&
        function_exists("curl_close");
    }
}