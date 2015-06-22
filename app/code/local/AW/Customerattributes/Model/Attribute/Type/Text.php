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


class AW_Customerattributes_Model_Attribute_Type_Text extends AW_Customerattributes_Model_Attribute_TypeAbstract
{
    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Grid_Text
     */
    protected function _getBackendGridRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Grid_Text();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Form_Text
     */
    protected function _getBackendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Form_Text();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Frontend_Form_Text
     */
    protected function _getFrontendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Frontend_Form_Text();
    }


    public function getValueType()
    {
        return AW_Customerattributes_Model_Resource_Value::VARCHAR_TYPE;
    }

    /**
     * @param mixed $value
     *
     * @throws Exception
     */
    public function validate($value)
    {
        $helper = Mage::helper('aw_customerattributes');
        $storeId = Mage::app()->getStore()->getId();
        $label = $this->getAttribute()->getLabel($storeId);
        $label = Mage::helper('core')->escapeHtml($label);
        if (strlen(trim($value)) < 1) {
            if ($this->getAttribute()->getData('validation_rules/is_required')) {
                throw new Exception($helper->__('%s is required', $label));
            }
        } else {
            //if value specified
            switch ($this->getAttribute()->getData('validation_rules/input_validation')) {
                case AW_Customerattributes_Model_Source_Validation::ALPHANUMERIC_CODE:
                    if (!Zend_Validate::is($value, 'Alnum', array('allowWhiteSpace' => true))) {
                        throw new Exception(
                            $helper->__(
                                '%s can not contain characters other than letters, digits and spaces', $label
                            )
                        );
                    }
                    break;
                case AW_Customerattributes_Model_Source_Validation::NUMERIC_CODE:
                    if (!Zend_Validate::is($value, 'Digits')) {
                        throw new Exception($helper->__('%s contains not only digit characters', $label));
                    }
                    break;
                case AW_Customerattributes_Model_Source_Validation::ALPHA_CODE:
                    if (!Zend_Validate::is($value, 'Alpha')) {
                        throw new Exception($helper->__('%s has not only alphabetic characters', $label));
                    }
                    break;
                case AW_Customerattributes_Model_Source_Validation::EMAIL_CODE:
                    if (!Zend_Validate::is($value, 'EmailAddress')) {
                        throw new Exception($helper->__('%s has invalid email address', $label));
                    }
                    break;
                default:
            }
            if ($this->getAttribute()->getData('validation_rules/is_unique')) {
                if (!Mage::helper('aw_customerattributes')->isValueUnique($this, $value)) {
                    throw new Exception($helper->__('The value of "%s" must be unique', $label));
                }
            }
        }
        if ($length = $this->getAttribute()->getData('validation_rules/minimum_text_length')) {
            if (!Zend_Validate::is($value, 'StringLength', array('min' => $length, 'max' => null))) {
                throw new Exception($helper->__("%s is less than '%s' symbols", $label, $length));
            }
        }
        if ($length = $this->getAttribute()->getData('validation_rules/maximum_text_length')) {
            if (!Zend_Validate::is($value, 'StringLength', array('min' => 0, 'max' => $length))) {
                throw new Exception($helper->__("%s is greater than '%s' symbols", $label, $length));
            }
        }
        return;
    }
}