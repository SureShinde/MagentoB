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

class AW_Customerattributes_Block_Widget_Backend_Form_Attachment_Renderer extends Varien_Data_Form_Element_File
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
            $html = '<a href="' . $url . '">' . $this->getValue() . '</a> ';

        }
        $html .= parent::getElementHtml();
        if (!$this->getRequired()) {
            $html .= $this->_getDeleteCheckbox();
        } else {
            $html .= $this->_getHiddenInput();
        }
        return $html;
    }

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

    /**
     * Enter description here...
     *
     * @return string
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ($this->getValue()) {
            $checkboxData = array(
                'type'  => 'checkbox',
                'name'  => parent::getName() . "[delete]",
                'id'    => $this->getHtmlId() . "_delete",
                'class' => 'checkbox',
                'value' => '1',
            );
            if ($this->getDisabled()) {
                $checkboxData['disabled'] = "disabled";
            }
            $html .= '<span class="delete-image">';
            $html .= "<input ";
            foreach ($checkboxData as $key => $value) {
                $html .= "{$key}=\"{$value}\"";
            }
            $html .= " />";
            $html .= '<label for="' . $this->getHtmlId() . '_delete"'
                . ($this->getDisabled() ? ' class="disabled"' : '') . '>'
            ;
            $html .= Mage::helper('aw_customerattributes')->__('Delete Attachment');
            $html .= '</label>';
            $html .= $this->_getHiddenInput();
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    protected function _getHiddenInput()
    {
        return '<input type="hidden" name="' . parent::getName() . '[value]" value="' . $this->getValue() . '" />';
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }
}