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


interface AW_Customerattributes_Model_Attribute_TypeInterface
{
    /**
     * @return AW_Customerattribute_Block_Widget_Backend_GridInterface
     */
    public function getBackendGridRenderer();

    /**
     * @return AW_Customerattribute_Block_Widget_Backend_FormInterface
     */
    public function getBackendFormRenderer();

    /**
     * @return AW_Customerattribute_Block_Widget_Frontend_FormInterface
     */
    public function getFrontendFormRenderer();

    /**
     * @param AW_Customerattributes_Model_Value $valueModel
     *
     * @return AW_Customerattributes_Model_Value
     */
    public function beforeSave($valueModel);

    /**
     * @return AW_Customerattributes_Model_Attribute_TypeInterface
     */
    public function afterAttributeDelete();

    /**
     * @param mixed $value
     *
     * @throws Exception
     */
    public function validate($value);

    /**
     * @param AW_Customerattributes_Model_Attribute $attribute
     *
     * @return AW_Customerattributes_Model_Attribute_TypeAbstract
     */
    public function setAttribute(AW_Customerattributes_Model_Attribute $attribute);

    /**
     * @return AW_Customerattributes_Model_Attribute_TypeAbstract
     */
    public function getAttribute();

    /**
     * @return int|varchar|text|date
     */
    public function getValueType();
}