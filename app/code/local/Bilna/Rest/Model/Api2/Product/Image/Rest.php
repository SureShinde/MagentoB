<?php
/**
 * Description of Rest_Bilna_Model_Api2_Product_Image_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Product_Image_Rest extends Bilna_Rest_Model_Api2_Product_Rest {
    /**
     * Allowed MIME types for image
     *
     * @var array
     */
    protected $_mimeTypes = array(
        'image/jpg'  => 'jpg',
        'image/jpeg' => 'jpg',
        'image/gif'  => 'gif',
        'image/png'  => 'png'
    );

    /**
     * Retrieve product image data for customer and guest roles
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        $imageData = array();
        $imageId = (int)$this->getRequest()->getParam('image');
        $galleryData = $this->_getProduct()->getData(self::GALLERY_ATTRIBUTE_CODE);

        if (!isset($galleryData['images']) || !is_array($galleryData['images'])) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        foreach ($galleryData['images'] as $image) {
            if ($image['value_id'] == $imageId && !$image['disabled']) {
                $imageData = $this->_formatImageData($image);
                break;
            }
        }
        if (empty($imageData)) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $imageData;
    }

    /**
     * Retrieve product images data for customer and guest
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $images = array();
        $galleryData = $this->_getProduct()->getData(self::GALLERY_ATTRIBUTE_CODE);
        if (isset($galleryData['images']) && is_array($galleryData['images'])) {
            foreach ($galleryData['images'] as $image) {
                if (!$image['disabled']) {
                    $images[] = $this->_formatImageData($image);
                }
            }
        }
        return $images;
    }

    /**
     * Retrieve media gallery
     *
     * @throws Mage_Api2_Exception
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Media
     */
    protected function _getMediaGallery()
    {
        $attributes = $this->_getProduct()->getTypeInstance(true)->getSetAttributes($this->_getProduct());

        if (!isset($attributes[self::GALLERY_ATTRIBUTE_CODE])
            || !$attributes[self::GALLERY_ATTRIBUTE_CODE] instanceof Mage_Eav_Model_Entity_Attribute_Abstract
        ) {
            $this->_critical('Requested product does not support images', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        $galleryAttribute = $attributes[self::GALLERY_ATTRIBUTE_CODE];
        /** @var $mediaGallery Mage_Catalog_Model_Product_Attribute_Backend_Media */
        $mediaGallery = $galleryAttribute->getBackend();
        return $mediaGallery;
    }

    /**
     * Create file name from received data
     *
     * @param array $data
     * @return string
     */
    protected function _getFileName($data)
    {
        $fileName = 'image';
        if (isset($data['file_name']) && $data['file_name']) {
            $fileName = $data['file_name'];
        }
        $fileName .= '.' . $this->_getExtensionByMimeType($data['file_mime_type']);
        return $fileName;
    }

    /**
     * Retrieve file extension using MIME type
     *
     * @throws Mage_Api2_Exception
     * @param string $mimeType
     * @return string
     */
    protected function _getExtensionByMimeType($mimeType)
    {
        if (!array_key_exists($mimeType, $this->_mimeTypes)) {
            $this->_critical('Unsuppoted image MIME type', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        return $this->_mimeTypes[$mimeType];
    }

    /**
     * Get file URI by its id. File URI is used by media backend to identify image
     *
     * @throws Mage_Api2_Exception
     * @param int $imageId
     * @return string
     */
    protected function _getImageFileById($imageId)
    {
        $file = null;
        $mediaGalleryData = $this->_getProduct()->getData('media_gallery');
        if (!isset($mediaGalleryData['images'])) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        foreach ($mediaGalleryData['images'] as $image) {
            if ($image['value_id'] == $imageId) {
                $file = $image['file'];
                break;
            }
        }
        if (!($file && $this->_getMediaGallery()->getImage($this->_getProduct(), $file))) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $file;
    }
}