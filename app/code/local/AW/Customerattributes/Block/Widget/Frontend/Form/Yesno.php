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

class AW_Customerattributes_Block_Widget_Frontend_Form_Yesno
    extends AW_Customerattributes_Block_Widget_Frontend_FormAbstract
{
    public function getHtml()
    {
        if (!$this->getProperty('is_editable_by_customer') && !$this->_getValue()) {
            return '';
        }
        $labelHtml = $this->_getLabelHtml();
        $selectHtml = $this->_getSelectHtml();
        $html = "
            {$labelHtml}
            <div class=\"input-box\">
                {$selectHtml}
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

    protected function _getSelectHtml()
    {
        $selectHtml = "";
        if ($this->getProperty('is_editable_by_customer')) {
            $selectData = array(
                'name'  => $this->_getCode(),
                'id'    => $this->_getCode(),
                'title' => $this->_getLabel(),
                'class' => ''
            );
            if ($this->getProperty('validation_rules/is_required')) {
                $selectData['class'] .= "required-entry";
            }

            $selectHtml = "<select ";
            foreach ($selectData as $key => $value) {
                $selectHtml .= "{$key}=\"{$value}\"";
            }
            $selectHtml .= ">";
            $optionSource = Mage::helper('aw_customerattributes/options')->getOptionsForYesnoAttribute(true, true);
            foreach ($optionSource as $value => $label) {
                $selectHtml .= "<option value=\"{$value}\"";
                if ($this->_getValue() == $value) {
                    $selectHtml .= " selected";
                }
                $selectHtml .= ">{$label}</option>";
            }
            $selectHtml .= "</select>";
        } else {
            $optionSource = Mage::helper('aw_customerattributes/options')->getOptionsForYesnoAttribute(true, true);
            foreach ($optionSource as $value => $label) {
                if ($this->_getValue() == $value) {
                    $selectHtml = $label;
                    break;
                }
            }
        }
        return $selectHtml;
    }
}