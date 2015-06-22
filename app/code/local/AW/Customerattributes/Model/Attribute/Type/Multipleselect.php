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


class AW_Customerattributes_Model_Attribute_Type_Multipleselect
    extends AW_Customerattributes_Model_Attribute_TypeAbstract
{
    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Grid_Multipleselect
     */
    protected function _getBackendGridRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Grid_Multipleselect();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Form_Multipleselect
     */
    protected function _getBackendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Form_Multipleselect();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Frontend_Form_Multipleselect
     */
    protected function _getFrontendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Frontend_Form_Multipleselect();
    }

    public function getValueType()
    {
        return AW_Customerattributes_Model_Resource_Value::TEXT_TYPE;
    }

    /**
     * @param mixed $value
     *
     * @throws Exception
     */
    public function validate($value)
    {
        if (!is_array($value)) {
            if (strlen($value) > 0) {
                $value = explode(',', $value);
            } else {
                //not selected
                $value = '0';
            }
        }
        $helper = Mage::helper('aw_customerattributes');
        $storeId = Mage::app()->getStore()->getId();
        $label = $this->getAttribute()->getLabel($storeId);
        $label = Mage::helper('core')->escapeHtml($label);
        if ($value === '0') {
            if ($this->getAttribute()->getData('validation_rules/is_required')) {
                throw new Exception($helper->__('%s is required', $label));
            }
        } else {
            $options = $this->getAttribute()->getStoreOptions();
            foreach ($value as $valueItem) {
                if (!array_key_exists($valueItem, $options)) {
                    throw new Exception($helper->__('%s has incorrect selected option', $label));
                }
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
        if (is_array($value)) {
            $value = implode(',', $value);
        } else {
            //not selected
            $value = '0';
        }
        $valueModel->setData('value', $value);
        return $valueModel;
    }
}