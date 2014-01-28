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

class AW_Customerattributes_Block_Widget_Frontend_Form_Attachment
    extends AW_Customerattributes_Block_Widget_Frontend_FormAbstract
{
    public function getHtml()
    {
        if (!$this->getProperty('is_editable_by_customer') && !$this->_getValue()) {
            return '';
        }
        $labelHtml = $this->_getLabelHtml();
        $displayHtml = $this->_getFileHtml();
        $inputHtml = "";
        $noteHtml = "";
        if ($this->getProperty('is_editable_by_customer')) {
            $inputHtml = $this->_getInputHtml();
            $noteHtml = $this->_getNoteHtml();
        }
        $html = "
            {$labelHtml}
            <div class=\"input-box\" style=\"width:auto\">
                {$displayHtml}
                {$inputHtml}
                {$noteHtml}
            </div>
        ";
        return $html;
    }

    protected function _getLabelHtml()
    {
        $labelHtml = "<label for=\"{$this->_getCode()}\"";
        if ($this->getProperty('validation_rules/is_required')) {
            $labelHtml .= "class=\"required\"><em>*</em>";
        } else {
            $labelHtml .= ">";
        }
        $labelHtml .= "{$this->_getLabel()}</label>";
        return $labelHtml;
    }

    protected function _getInputHtml()
    {
        $inputData = array(
            'type'  => 'file',
            'name'  => $this->_getCode(),
            'id'    => $this->_getCode(),
            'title' => $this->_getLabel(),
            'class' => ''
        );

        if (!$this->_getValue() && $this->getProperty('validation_rules/is_required')) {
            $inputData['class'] .= "required-entry";
        }

        $inputHtml = "<input ";
        foreach ($inputData as $key => $value) {
            $inputHtml .= "{$key}=\"{$value}\"";
        }
        $inputHtml .= " />";

        if ($this->_getValue()) {
            $customer = Mage::helper('customer')->getCustomer();
            $attachmentPath = Mage::helper('aw_customerattributes/image')->getAttachmentPath(
                $this->getProperty('code'),
                $this->_getValue(),
                $customer->getId()
            );
            if (is_readable($attachmentPath)) {
                //add hidden field for stored value
                $inputData = array(
                    'type'  => 'hidden',
                    'name'  => $this->_getCode() . "[value]",
                    'id'    => $this->_getCode() . '_value',
                    'class' => '',
                    'value' => $this->_getValue()
                );
                $inputHtml .= "<input ";
                foreach ($inputData as $key => $value) {
                    $inputHtml .= "{$key}=\"{$value}\"";
                }
                $inputHtml .= " />";
                if (!$this->getProperty('validation_rules/is_required')) {
                    //add checkbox for delete uploaded attachment
                    $checkboxData = array(
                        'type'  => 'checkbox',
                        'name'  => $this->_getCode() . "[delete]",
                        'id'    => $this->_getCode() . "_delete",
                        'title' => $this->_getLabel(),
                        'class' => 'checkbox',
                        'value' => '1'
                    );
                    $checkboxHtml = "<input ";
                    foreach ($checkboxData as $key => $value) {
                        $checkboxHtml .= "{$key}=\"{$value}\"";
                    }
                    $checkboxHtml .= " />";
                    $labelText = Mage::helper('aw_customerattributes')->__('Delete File');
                    $checkboxHtml .= "<label style=\"float:none\" for=\"{$checkboxData['id']}\">{$labelText}</label>";
                    $inputHtml .= "<div class=\"control\">{$checkboxHtml}</div>";
                }
            }
        }

        return $inputHtml;
    }

    protected function _getFileHtml()
    {
        $html = "";
        if ($this->_getValue()) {
            $customer = Mage::helper('customer')->getCustomer();
            $attachmentPath = Mage::helper('aw_customerattributes/image')->getAttachmentPath(
                $this->getProperty('code'),
                $this->_getValue(),
                $customer->getId()
            );
            if (is_readable($attachmentPath)) {
                $url = Mage::getUrl(
                    'aw_customerattributes/customer/downloadAttachment',
                    array('attribute_code' => $this->getProperty('code'))
                );
                $helper = Mage::helper('aw_customerattributes');
                $html = "<a href=\"{$url}\" title=\"{$this->_getValue()}\">" . $helper->__('Download') . "</a><br />";
            }
        }
        return $html;
    }

    protected function _getNoteHtml()
    {
        $helper = Mage::helper('aw_customerattributes');
        $noteHtml = '';
        $allowedFileExtensions = $this->getTypeModel()->getAllowedFileExtensions();
        if (count($allowedFileExtensions) > 0) {
            $allowedFileExtensions = implode(', ', $allowedFileExtensions);
            $allowedFileExtensions = "<strong>{$allowedFileExtensions}</strong>";
            $noteHtml .= "<div>" .
                $helper->__('Allowed file extensions to upload: %s', $allowedFileExtensions) .
                "</div>";
        }
        $maxFileSize = intval($this->getProperty('validation_rules/filesize'));
        if ($maxFileSize > 0) {
            $maxFileSize = "<strong>{$maxFileSize}</strong>";
            $noteHtml .= "<div>" .
                $helper->__('Maximum allowed file size to upload (kilobytes): %s', $maxFileSize) .
                "</div>";
        }
        return $noteHtml;
    }

    /**
     * getter
     *
     * @return mixed
     */
    protected function _getValue()
    {
        if (is_null($this->_value)) {
            return $this->getProperty('default_value');
        }
        return $this->_value;
    }
}