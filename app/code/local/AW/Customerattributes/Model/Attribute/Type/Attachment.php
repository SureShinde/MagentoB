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


class AW_Customerattributes_Model_Attribute_Type_Attachment extends AW_Customerattributes_Model_Attribute_TypeAbstract
{
    protected $_maxFileSize = 0;

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Grid_Attachment
     */
    protected function _getBackendGridRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Grid_Attachment();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Form_Attachment
     */
    protected function _getBackendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Form_Attachment();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Frontend_Form_Attachment
     */
    protected function _getFrontendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Frontend_Form_Attachment();
    }

    public function getValueType()
    {
        return AW_Customerattributes_Model_Resource_Value::TEXT_TYPE;
    }

    /**
     * @param AW_Customerattributes_Model_Value $valueModel
     *
     * @return AW_Customerattributes_Model_Value
     * @throws Exception
     */
    public function beforeSave($valueModel)
    {
        $code = AW_Customerattributes_Model_Attribute_TypeAbstract::FRONTEND_ATTRIBUTE_CODE_PREFIX .
            $this->getAttribute()->getData('code');
        //set original file name
        $attribute = Mage::app()->getRequest()->getParam($code, null);
        if (is_array($attribute) && isset($attribute['value'])) {
            $valueModel->setData('value', $attribute['value']);
        }
        $isFileUploaded = false;
        if (array_key_exists($code, $_FILES) && array_key_exists('tmp_name', $_FILES[$code])) {
            $isFileUploaded = !!$_FILES[$code]['tmp_name'];
        }
        if (!$isFileUploaded) {
            //if attribute is required and value is not specified
            if (
                $this->getAttribute()->getData('validation_rules/is_required')
                && strlen(trim($valueModel->getData('value'))) < 1
            ) {
                $helper = Mage::helper('aw_customerattributes');
                $storeId = Mage::app()->getStore()->getId();
                $label = $this->getAttribute()->getLabel($storeId);
                $label = Mage::helper('core')->escapeHtml($label);
                throw new Exception($helper->__('%s is required', $label));
            }
            //if delete param sended and attribute is not required
            if (
                is_array($attribute) && isset($attribute['delete'])
                && $attribute['delete']
                && !$this->getAttribute()->getData('validation_rules/is_required')
            ) {
                if (!is_null($valueModel->getData('value')) && strlen(trim($valueModel->getData('value'))) > 0) {
                    $this->_deleteAttachment($valueModel);
                }
                $valueModel->setData('value', '');
            }
            return $valueModel;
        }

        $uploader = new Varien_File_Uploader($code);
        $allowedFileExtensions = $this->getAllowedFileExtensions();
        if (count($allowedFileExtensions) > 0) {
            $uploader->setAllowedExtensions($allowedFileExtensions);
        }
        $uploader->setAllowCreateFolders(true);
        $this->_maxFileSize = intval($this->getAttribute()->getData('validation_rules/filesize'));
        $uploader->addValidateCallback('size', $this, 'validateMaxSize');

        // Set media as the upload dir
        $attachmentPath = Mage::helper('aw_customerattributes/image')->getAttachmentPath(
            $this->getAttribute()->getData('code'),
            '',
            $valueModel->getData('customer_id')
        );
        // Upload the image
        $uploader->save($attachmentPath, $uploader->getUploadedFileName());
        $uploadedFileName = $uploader->getUploadedFileName();

        if ($valueModel->getData('value') !== $uploadedFileName) {
            //delete old attachment
            $this->_deleteAttachment($valueModel);
        }
        if ($uploadedFileName) {
            $valueModel->setData('value', $uploadedFileName);
        }
        return $valueModel;
    }

    /**
     * @return AW_Customerattributes_Model_Attribute_Type_Attachment
     */
    public function afterAttributeDelete()
    {
        //get dir of all attachments by attribute
        $attachmentPath = Mage::helper('aw_customerattributes/image')
            ->getAttachmentPath($this->getAttribute()->getData('code'), '', '');
        //protection from removing all directories
        if (strlen($attachmentPath) > 0 && strpos($attachmentPath, $this->getAttribute()->getData('code')) !== false) {
            Mage::helper('aw_customerattributes/image')->removeDir($attachmentPath);
        }
        return $this;
    }

    public function validateMaxSize($filePath)
    {
        if ($this->_maxFileSize > 0 && filesize($filePath) > ($this->_maxFileSize * 1024)) {
            throw new Exception(
                Mage::helper('aw_customerattributes')->__(
                    'Uploaded file is larger than %s kilobytes allowed by server', $this->_maxFileSize
                )
            );
        }
    }

    public function getAllowedFileExtensions()
    {
        $allowedFileExtensions = $this->getAttribute()->getData('validation_rules/allowed_file_extensions');
        if (strlen(trim($allowedFileExtensions)) > 0) {
            $extensions = explode(',', $allowedFileExtensions);
            foreach ($extensions as $key => $value) {
                $extensions[$key] = trim($value);
            }
            return $extensions;
        }
        return array();
    }

    /**
     * @param AW_Customerattributes_Model_Value $valueModel
     */
    private function _deleteAttachment($valueModel)
    {
        $attachmentPath = Mage::helper('aw_customerattributes/image')->getAttachmentPath(
            $this->getAttribute()->getData('code'),
            $valueModel->getData('value'),
            $valueModel->getData('customer_id')
        );
        if (is_writable($attachmentPath)) {
            @unlink($attachmentPath);
        }
    }
}