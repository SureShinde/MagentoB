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

class AW_Customerattributes_Block_Widget_Frontend_Form_Text
    extends AW_Customerattributes_Block_Widget_Frontend_FormAbstract
{
    public function getHtml()
    {
        if (!$this->getProperty('is_editable_by_customer') && !$this->_getValue()) {
            return '';
        }
        $labelHtml = $this->_getLabelHtml();
        $inputHtml = $this->_getInputHtml();
        $html = "
            {$labelHtml}
            <div class=\"input-box\">
                {$inputHtml}
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
        $inputHtml = "";
        if ($this->getProperty('is_editable_by_customer')) {
            $inputData = array(
                'type'  => 'text',
                'name'  => $this->_getCode(),
                'id'    => $this->_getCode(),
                'title' => $this->_getLabel(),
                'class' => 'input-text',
                'value' => htmlspecialchars($this->_getValue())
            );
            if ($classNames = $this->_getValidateCssClassNames()) {
                $inputData['class'] .= ' ' . $classNames;
            }

            $inputHtml = "<input ";
            foreach ($inputData as $key => $value) {
                $inputHtml .= "{$key}=\"{$value}\"";
            }
            $inputHtml .= " />";
        } else {
            $inputHtml = htmlspecialchars($this->_getValue());
        }
        return $inputHtml;
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
        switch ($this->getProperty('validation_rules/input_validation')) {
            case AW_Customerattributes_Model_Source_Validation::ALPHANUMERIC_CODE:
                $result .= "validate-alphanum-with-spaces ";
                break;
            case AW_Customerattributes_Model_Source_Validation::NUMERIC_CODE:
                $result .= "validate-number ";
                break;
            case AW_Customerattributes_Model_Source_Validation::ALPHA_CODE:
                $result .= "validate-alpha ";
                break;
            case AW_Customerattributes_Model_Source_Validation::URL_CODE:
                $result .= "validate-url ";
                break;
            case AW_Customerattributes_Model_Source_Validation::EMAIL_CODE:
                $result .= "validate-email ";
                break;
            default:
        }
        return trim($result);
    }
}