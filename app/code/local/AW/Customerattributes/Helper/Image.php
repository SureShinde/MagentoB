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
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Customerattributes_Helper_Image extends Mage_Core_Helper_Data
{
    public function resizeImage($attributeCode, $imageName, $customerId, $width = 50, $height = null)
    {
        $originalImagePath = $this->getAttachmentPath($attributeCode, $imageName, $customerId);
        if (is_null($width) && is_null($height)) {
            list($width, $height) = getimagesize($originalImagePath);
        }
        if (file_exists($originalImagePath) && is_file($originalImagePath)) {
            $cachedImagePath = $this->getCachedImagePath($attributeCode, $imageName, $customerId, $width, $height);
            if (!$this->isCached($cachedImagePath)) {
                $imageObj = new Varien_Image($originalImagePath);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(false);
                $imageObj->backgroundColor(array(255, 255, 255));
                try {
                    $imageObj->resize($width, $height);
                    $imageObj->save($cachedImagePath);
                } catch (Exception $e) {
                    return Mage::helper('aw_customerattributes')->__('No Image');
                }
            }
            return $this->getCahcedImageUrl($attributeCode, $imageName, $customerId, $width, $height);
        }
        return Mage::helper('aw_customerattributes')->__('No Image');
    }

    public function getAttachmentPath($attributeCode, $fileName, $customerId)
    {
        return $this->getBaseAttachmentFolderPath()
            . DS . 'attribute'
            . DS . $attributeCode
            . DS . $customerId
            . DS . $fileName
        ;
    }

    public function getBaseAttachmentFolderPath()
    {
        return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'aw_customerattributes';
    }

    public function getCacheImageFolderUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'aw_customerattributes' . DS . 'cache';
    }

    public function getCachedImagePath($attributeCode, $imageName, $customerId, $width, $height)
    {
        return $cachedImagePath = $this->getBaseAttachmentFolderPath()
            . DS . 'cache'
            . DS . $attributeCode
            . DS . $customerId
            . DS . $width . 'x' . (!is_null($height) ? $height : '')
            . DS . $imageName
        ;
    }

    public function getCahcedImageUrl($attributeCode, $imageName, $customerId, $width, $height)
    {
        return $this->getCacheImageFolderUrl()
            . DS . $attributeCode
            . DS . $customerId
            . DS . $width . 'x' . (!is_null($height) ? $height : '')
            . DS . $imageName
        ;
    }

    public function isCached($cachedImagePath)
    {
        if (file_exists($cachedImagePath) && is_file($cachedImagePath)) {
            return true;
        }
        return false;
    }

    public function cleanImageCache()
    {
        $cacheImageDir = $this->getBaseAttachmentFolderPath() . DS . 'cache';
        return $this->removeDir($cacheImageDir);
    }

    static public function removeDir($path)
    {
        if (is_file($path)) {
            @unlink($path);
        } else {
            array_map(array('AW_Customerattributes_Helper_Image', 'removeDir'), glob($path . '/*'));
        }
        return @rmdir($path);
    }

    function viewFile($attribute, $customer)
    {
        $attributeCollection = Mage::helper('aw_customerattributes/customer')
            ->getAttributeValueCollectionForCustomer($customer);
        $attributeCollection->addAttributeFilter($attribute->getId());
        $attributeValue = $attributeCollection->getFirstItem();

        $path = $this->getBaseAttachmentFolderPath()
            . DS . 'attribute'
            . DS . $attribute->getCode()
            . DS . $customer->getId()
            . DS
        ;

        $file = $attributeValue->getValue();
        $fileName = $path . $file;

        $ioFile = new Varien_Io_File();
        if (!$ioFile->fileExists($fileName) || strpos($fileName, $path) !== 0) {
            return null;
        }
        $ioFile->open(array('path' => $path));
        $fileName = $ioFile->getCleanPath($fileName);

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'gif':
                $contentType = 'image/gif';
                break;
            case 'jpg':
            case 'jpeg':
                $contentType = 'image/jpeg';
                break;
            case 'png':
                $contentType = 'image/png';
                break;
            default:
                $contentType = 'application/octet-stream';
                break;
        }

        $ioFile->streamOpen($fileName, 'r');
        $contentLength = $ioFile->streamStat('size');
        $contentModify = $ioFile->streamStat('mtime');

        $result = array(
            'content_stream' => $ioFile,
            'header'         => array(
                'content_type'     => $contentType,
                'content_length'   => $contentLength,
                'content_modified' => date('r', $contentModify),
            )
        );
        if ($attribute->getType() == 'attachment') {
            $result['filename'] = $file;
        }
        return $result;
    }
}