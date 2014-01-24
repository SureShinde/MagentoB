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


class AW_Customerattributes_Model_Attribute_Type_Image extends AW_Customerattributes_Model_Attribute_Type_Attachment
{
    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Grid_Image
     */
    protected function _getBackendGridRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Grid_Image();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Form_Image
     */
    protected function _getBackendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Form_Image();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Frontend_Form_Image
     */
    protected function _getFrontendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Frontend_Form_Image();
    }

    public function getValueType()
    {
        return AW_Customerattributes_Model_Resource_Value::TEXT_TYPE;
    }

    public function getAllowedFileExtensions()
    {
        return array('jpg', 'jpeg', 'gif', 'png');
    }
}