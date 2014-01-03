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

interface AW_Customerattributes_Block_Widget_Backend_FormInterface
{
    /**
     * @return string
     */
    public function getFieldId();

    /**
     * @return string
     */
    public function getFieldType();

    /**
     * @return string|null
     */
    public function getFieldTypeRenderer();

    /**
     * @return array
     */
    public function getFieldProperties();

    /**
     * @param AW_Customerattributes_Model_Attribute_TypeInterface $type
     *
     * @return AW_Customerattributes_Block_Widget_Backend_FormAbstract
     */
    public function setTypeModel(AW_Customerattributes_Model_Attribute_TypeInterface $type);

    /**
     * @return AW_Customerattributes_Model_Attribute_TypeInterface
     */
    public function getTypeModel();

    /**
     * @param mixed $value
     *
     * @return AW_Customerattributes_Block_Widget_Backend_FormInterface
     */
    public function setValue($value);
}