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


class AW_Customerattributes_Model_Attribute_Type_Date extends AW_Customerattributes_Model_Attribute_TypeAbstract
{
    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Grid_Date
     */
    protected function _getBackendGridRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Grid_Date();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Form_Date
     */
    protected function _getBackendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Form_Date();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Frontend_Form_Date
     */
    protected function _getFrontendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Frontend_Form_Date();
    }

    public function getValueType()
    {
        return AW_Customerattributes_Model_Resource_Value::DATE_TYPE;
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
            try {
                Mage::app()->getLocale()->date(
                    $value,
                    Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
                );
            } catch (Exception $e) {
                throw new Exception($helper->__('%s is invalid', $label));
            }
        }
        return;
    }

    /**
     * @param AW_Customerattributes_Model_Value $valueModel
     *
     * @return AW_Customerattributes_Model_Value
     */
    public function beforeSave($valueModel)
    {
        $value = $valueModel->getData('value');
        if (strlen(trim($value)) > 0) {
            $zDate = Mage::app()->getLocale()->date(
                $value,
                Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
            );
            $value = $zDate->toString(Zend_Date::W3C);
        } else {
            $value = null;
        }
        $valueModel->setData('value', $value);
        return $valueModel;
    }
}