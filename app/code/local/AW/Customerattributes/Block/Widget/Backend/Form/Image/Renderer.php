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

class AW_Customerattributes_Block_Widget_Backend_Form_Image_Renderer extends Varien_Data_Form_Element_Image
{
    /**
     * Enter description here...
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        if ($this->getValue()) {
            $this->setClass('input-file');
            $url = $this->_getUrl();
            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                $url = Mage::getBaseUrl('media') . $url;
            }
            $html = '<a href="' . $url . '" onclick="imagePreview(\'' . $this->getHtmlId()
                . '_image\'); return false;"><img src="' . $url . '" id="' . $this->getHtmlId() . '_image" title="'
                . $this->getValue() . '" alt="' . $this->getValue()
                . '" height="22" width="22" class="small-image-preview v-middle" /></a> '
            ;

        }
        $html .= '<input id="' . $this->getHtmlId() . '" name="' . $this->getName()
            . '" value="' . $this->getEscapedValue() . '" ' . $this->serialize($this->getHtmlAttributes()) . '/>'
            . "\n"
        ;
        $html .= $this->getAfterElementHtml();
        if (!$this->getRequired()) {
            $html .= $this->_getDeleteCheckbox();
        } else {
            $html .= $this->_getHiddenInput();
        }
        return $html;
    }

    /**
     * @return string
     */
    protected function _getUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl(
            'aw_customerattributes_admin/adminhtml_customer/downloadAttachment',
            array(
                'attribute_code' => $this->getAttributeCode(),
                'customer_id'    => Mage::registry('current_customer')->getId(),
            )
        );
    }
}