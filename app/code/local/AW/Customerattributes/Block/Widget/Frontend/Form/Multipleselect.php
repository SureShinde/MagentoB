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

class AW_Customerattributes_Block_Widget_Frontend_Form_Multipleselect
    extends AW_Customerattributes_Block_Widget_Frontend_FormAbstract
{
    public function getHtml()
    {
        $value = $this->_getValue();
        if (
            !$this->getProperty('is_editable_by_customer')
            && ((count($value) === 0) || (count($value) === 1 && isset($value[0]) && $value[0] === '0'))
        ) {
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
                'name'     => $this->_getCode() . '[]',
                'id'       => $this->_getCode(),
                'title'    => $this->_getLabel(),
                'class'    => '',
                'multiple' => 'multiple'
            );
            if (!$this->getProperty('is_editable_by_customer')) {
                $selectData['disabled'] = "1";
            }
            if ($this->getProperty('validation_rules/is_required')) {
                $selectData['class'] .= "required-entry";
            }

            $selectHtml = "<select ";
            foreach ($selectData as $key => $value) {
                $selectHtml .= "{$key}=\"{$value}\"";
            }
            $selectHtml .= ">";
            foreach ($this->_getOptionsForSelect() as $value => $label) {
                $selectHtml .= "<option value=\"{$value}\"";
                if (in_array($value, $this->_getValue())) {
                    $selectHtml .= " selected";
                    //fix for identical color of text and bkg
                    if (!$this->getProperty('is_editable_by_customer')) {
                        $selectHtml .= " style='color:white'";
                    }
                }
                $selectHtml .= ">{$label}</option>";
            }
            $selectHtml .= "</select>";
        } else {
            $labels = array();
            foreach ($this->_getOptionsForSelect() as $value => $label) {
                if (in_array($value, $this->_getValue())) {
                    $labels[] = $label;
                }
            }
            $selectHtml = implode(', ', $labels);
        }
        return $selectHtml;
    }

    /**
     * getter
     *
     * @return mixed
     */
    protected function _getValue()
    {
        $value = parent::_getValue();
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    private function _getOptionsForSelect()
    {
        if (is_null($this->getTypeModel())) {
            return null;
        }
        $attribute = $this->getTypeModel()->getAttribute();
        if (is_null($attribute)) {
            return null;
        }
        $storeId = Mage::app()->getStore()->getId();
        $options = Mage::helper('aw_customerattributes/options')
            ->getOptionsForAttributeByStoreId($attribute, $storeId, false);
        foreach ($options as $key => $value) {
            $options[$key] = htmlspecialchars($value);
        }
        return $options;
    }
}