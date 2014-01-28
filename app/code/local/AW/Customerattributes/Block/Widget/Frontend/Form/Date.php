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

class AW_Customerattributes_Block_Widget_Frontend_Form_Date
    extends AW_Customerattributes_Block_Widget_Frontend_FormAbstract
{
    public function getHtml()
    {
        $dateHtml = "";
        $value = $this->_getValue();
        if ($this->getProperty('is_editable_by_customer')) {
            //DATETIME PICKER
            $className = 'datetime-picker input-text';
            if ($this->getProperty('validation_rules/is_required')) {
                $className .= " required-entry";
            }
            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $calendar = Mage::app()->getLayout()
                ->createBlock('core/html_date')
                ->setId($this->_getCode())
                ->setName($this->_getCode())
                ->setClass($className)
                ->setImage(Mage::getDesign()->getSkinUrl('images/calendar.gif'))
                ->setFormat($format)
                ->setExtraParams("style='width:235px'");
            if ($value instanceof Zend_Date) {
                $calendar->setValue($value->toString($format));
            } elseif (is_string($value)) {
                $calendar->setValue($value);
            }
            $dateHtml = $calendar->getHtml();
        } else {
            $dateHtml = $value;
            if (!is_null($value)) {
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_FULL);
                $dateHtml = $value->toString($format);
            } else {
                return '';
            }
        }

        //LABEL
        $labelHtml = "<label for=\"{$this->_getCode()}\"";
        if ($this->getProperty('validation_rules/is_required')) {
            $labelHtml .= "class=\"required\"><em>*</em>";
        } else {
            $labelHtml .= ">";
        }
        $labelHtml .= "{$this->_getLabel()}</label>";
        $html = "
            {$labelHtml}
            <div class=\"input-box field-row\">
                {$dateHtml}
            </div>
        ";
        return $html;
    }

    protected function _getValue()
    {
        $values = Mage::getSingleton('customer/session')->getAWCACustomerFormData();
        if (is_array($values) && array_key_exists($this->_getCode(), $values)) {
            return $values[$this->_getCode()];
        }
        if (is_null($this->_value)) {
            $value = $this->getProperty('default_value');
            if (!is_null($value) && strlen(trim($value)) > 0) {
                try {
                    return Mage::app()->getLocale()->date($value, Zend_Date::ISO_8601);
                } catch (Exception $e) {
                    return $value;
                }
            } else {
                return null;
            }
        }
        return Mage::app()->getLocale()->date($this->_value, Zend_Date::ISO_8601);
    }
}