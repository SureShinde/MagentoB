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

class AW_Customerattributes_Block_Widget_Backend_Form_Attachment
    extends AW_Customerattributes_Block_Widget_Backend_FormAbstract
{
    public function getFieldId()
    {
        return $this->_getCode();
    }

    public function getFieldType()
    {
        return 'attribute_file';
    }

    public function getFieldTypeRenderer()
    {
        return 'AW_Customerattributes_Block_Widget_Backend_Form_Attachment_Renderer';
    }

    public function getFieldProperties()
    {
        $properties = array(
            'label'          => $this->_getLabel(),
            'name'           => $this->_getCode(),
            'attribute_code' => $this->getProperty('code'),
            'required'       => $this->getProperty('validation_rules/is_required') ? true : false,
            'note'           => $this->_getNoteHtml(),
        );
        return $properties;
    }

    protected function _getNoteHtml()
    {
        $helper = Mage::helper('aw_customerattributes');
        $noteHtml = '';
        $allowedFileExtensions = $this->getTypeModel()->getAllowedFileExtensions();
        if (count($allowedFileExtensions) > 0) {
            $allowedFileExtensions = implode(', ', $allowedFileExtensions);
            $allowedFileExtensions = "<strong>{$allowedFileExtensions}</strong>";
            $noteHtml .= $helper->__('Allowed file extensions to upload: %s', $allowedFileExtensions);
            $noteHtml .= "\n";
        }
        $maxFileSize = intval($this->getProperty('validation_rules/filesize'));
        if ($maxFileSize > 0) {
            $maxFileSize = "<strong>{$maxFileSize}</strong>";
            $noteHtml .= $helper->__('Maximum allowed file size to upload (kilobytes): %s', $maxFileSize);
            $noteHtml .= "\n";
        }
        if (intval($this->getProperty('validation_rules/image_width')) > 0) {
            $width = intval($this->getProperty('validation_rules/image_width'));
            $width = "<strong>{$width}</strong>";
            $noteHtml .= $helper->__('Recommended image width (pixels): %s', $width);
            $noteHtml .= "\n";
        }
        if (intval($this->getProperty('validation_rules/image_height')) > 0) {
            $height = intval($this->getProperty('validation_rules/image_height'));
            $height = "<strong>{$height}</strong>";
            $noteHtml .= $helper->__('Recommended image height (pixels): %s', $height);
            $noteHtml .= "\n";
        }
        return $noteHtml;
    }
}