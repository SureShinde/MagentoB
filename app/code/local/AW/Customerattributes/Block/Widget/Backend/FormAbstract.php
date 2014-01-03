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

abstract class AW_Customerattributes_Block_Widget_Backend_FormAbstract
    implements AW_Customerattributes_Block_Widget_Backend_FormInterface
{
    /**
     * @var AW_Customerattributes_Model_Attribute_TypeInterface
     */
    protected $_type = null;

    protected $_value = null;

    /**
     * @param AW_Customerattributes_Model_Attribute_TypeInterface $type
     *
     * @return AW_Customerattributes_Block_Widget_Backend_FormAbstract
     */
    public function setTypeModel(AW_Customerattributes_Model_Attribute_TypeInterface $type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * @return AW_Customerattributes_Model_Attribute_TypeInterface
     */
    public function getTypeModel()
    {
        return $this->_type;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getProperty($key)
    {
        if (is_null($this->getTypeModel())) {
            return null;
        }
        if (is_null($this->getTypeModel()->getAttribute())) {
            return null;
        }
        return $this->getTypeModel()->getAttribute()->getData($key);
    }

    /**
     * @param mixed $value
     *
     * @return AW_Customerattributes_Block_Widget_Backend_FormAbstract
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * getter
     *
     * @return string
     */
    protected function _getLabel()
    {
        $storeId = Mage::app()->getStore()->getId();
        $label = $this->getTypeModel()->getAttribute()->getLabel($storeId);
        return Mage::helper('core')->escapeHtml($label);
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

    /**
     * @return string
     */
    protected function _getCode()
    {
        $attributePrefix = AW_Customerattributes_Model_Attribute_TypeAbstract::FRONTEND_ATTRIBUTE_CODE_PREFIX;
        return $attributePrefix .$this->getProperty('code');
    }
}