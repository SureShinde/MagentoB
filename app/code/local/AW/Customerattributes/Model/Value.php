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


class AW_Customerattributes_Model_Value extends Mage_Core_Model_Abstract
{
    protected $_attributeInstance = null;

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('aw_customerattributes/value');
    }

    /**
     * @return AW_Customerattributes_Model_Attribute|null
     */
    public function getAttributeModel()
    {
        if (is_null($this->_attributeInstance)) {
            $attributeId = $this->getData('attribute_id');
            $this->_attributeInstance = Mage::getModel('aw_customerattributes/attribute')->load($attributeId);
            if (is_null($this->_attributeInstance->getId())) {
                $this->_attributeInstance = null;
            }
        }
        return $this->_attributeInstance;
    }

    protected function _beforeSave()
    {
        if (!is_null($this->getAttributeModel())) {
            $typeModel = $this->getAttributeModel()->getTypeModel();
            if (!is_null($typeModel)) {
                $typeModel->beforeSave($this);
            }
        }
        return parent::_beforeSave();
    }
}