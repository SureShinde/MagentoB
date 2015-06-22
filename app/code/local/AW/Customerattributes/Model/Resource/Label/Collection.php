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


class AW_Customerattributes_Model_Resource_Label_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_customerattributes/label');
    }

    /**
     * @param $attribute AW_Customerattributes_Model_Attribute|int
     *
     * @return AW_Customerattributes_Model_Resource_Label_Collection
     */
    public function addAttributeFilter($attribute)
    {
        if ($attribute instanceof AW_Customerattributes_Model_Attribute) {
            $attributeId = $attribute->getId();
        } elseif (is_numeric($attribute)) {
            $attributeId = $attribute;
        } else {
            return $this;
        }
        $this->addFieldToFilter('attribute_id', $attributeId);
        return $this;
    }
}