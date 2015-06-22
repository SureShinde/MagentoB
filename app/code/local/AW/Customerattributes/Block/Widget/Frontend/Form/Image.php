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

class AW_Customerattributes_Block_Widget_Frontend_Form_Image
    extends AW_Customerattributes_Block_Widget_Frontend_Form_Attachment
{
    protected function _getFileHtml()
    {
        $imageHtml = "";
        if ($this->_getValue()) {
            $urlToView = Mage::getUrl(
                'aw_customerattributes/customer/downloadAttachment',
                array('attribute_code' => $this->getProperty('code'))
            );
            $imageData = array(
                'src'    => $urlToView,
                'alt'    => $this->_getLabel(),
                'id'     => $this->_getCode() . "_image",
                'title'  => $this->_getLabel(),
                'height' => '50'
            );
            $imageHtml = "<img ";
            foreach ($imageData as $key => $value) {
                $imageHtml .= "{$key}=\"{$value}\"";
            }
            $imageHtml .= " />";
            $imageHtml = "<a href=\"{$urlToView}\" target=\"_blank\">{$imageHtml}</a><br />";
        }
        return $imageHtml;
    }

    protected function _getNoteHtml()
    {
        $helper = Mage::helper('aw_customerattributes');
        $noteHtml = parent::_getNoteHtml();
        if (intval($this->getProperty('validation_rules/image_width')) > 0) {
            $width = intval($this->getProperty('validation_rules/image_width'));
            $width = "<strong>{$width}</strong>";
            $noteHtml .= "<div>" .
                $helper->__('Recommended image width (pixels): %s', $width) .
                "</div>";
        }
        if (intval($this->getProperty('validation_rules/image_height')) > 0) {
            $height = intval($this->getProperty('validation_rules/image_height'));
            $height = "<strong>{$height}</strong>";
            $noteHtml .= "<div>" .
                $helper->__('Recommended image height (pixels): %s', $height) .
                "</div>";
        }


        return $noteHtml;
    }
}