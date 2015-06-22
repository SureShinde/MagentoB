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

class AW_Customerattributes_Block_Widget_Backend_Form_Date
    extends AW_Customerattributes_Block_Widget_Backend_FormAbstract
{
    public function getFieldId()
    {
        return $this->_getCode();
    }

    public function getFieldType()
    {
        return 'date';
    }

    public function getFieldTypeRenderer()
    {
        return null;
    }

    public function getFieldProperties()
    {
        $properties = array(
            'label'        => $this->_getLabel(),
            'title'        => $this->_getLabel(),
            'name'         => $this->_getCode(),
            'image'        => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'format'       => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'required'     => $this->getProperty('validation_rules/is_required') ? true : false,
            'locale'       => Mage::app()->getLocale()->getLocaleCode(),
        );
        return $properties;
    }

}