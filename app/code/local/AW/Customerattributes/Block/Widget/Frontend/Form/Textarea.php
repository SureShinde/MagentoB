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

class AW_Customerattributes_Block_Widget_Frontend_Form_Textarea
    extends AW_Customerattributes_Block_Widget_Frontend_FormAbstract
{
    public function getHtml()
    {
        if (!$this->getProperty('is_editable_by_customer') && !$this->_getValue()) {
            return '';
        }
        $labelHtml = $this->_getLabelHtml();
        $textareaHtml = $this->_getTextareaHtml();
        $html = "
            {$labelHtml}
            <div class=\"input-box\">
                {$textareaHtml}
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

    protected function _getTextareaHtml()
    {
        $textareaHtml = "";
        if ($this->getProperty('is_editable_by_customer')) {
            $textareaData = array(
                'name'  => $this->_getCode(),
                'id'    => $this->_getCode(),
                'title' => $this->_getLabel(),
                'class' => ''
            );
            if ($classNames = $this->_getValidateCssClassNames()) {
                $textareaData['class'] .= ' ' . $classNames;
            }

            $textareaHtml = "<textarea ";
            foreach ($textareaData as $key => $value) {
                $textareaHtml .= "{$key}=\"{$value}\"";
            }
            $value = htmlspecialchars($this->_getValue());
            $textareaHtml .= ">{$value}</textarea>";
        } else {
            $textareaHtml = nl2br(htmlspecialchars($this->_getValue()));
        }
        return $textareaHtml;
    }

    protected function _getValidateCssClassNames()
    {
        $result = "";
        if ($this->getProperty('validation_rules/is_required')) {
            $result .= "required-entry ";
        }
        if (
            $this->getProperty('validation_rules/minimum_text_length')
            || $this->getProperty('validation_rules/maximum_text_length')
        ) {
            $result .= "aw-ca-validate-length ";
            if ($length = $this->getProperty('validation_rules/minimum_text_length')) {
                $result .= "minimum-length-" . (int)$length . " ";
            }
            if ($length = $this->getProperty('validation_rules/maximum_text_length')) {
                $result .= "maximum-length-" . (int)$length . " ";
            }
        }
        return trim($result);
    }
}