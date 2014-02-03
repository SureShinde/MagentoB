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


class AW_Customerattributes_Model_Attribute extends Mage_Core_Model_Abstract
{
    /**
     *
     */
    protected $_typeInstance = null;

    /**
     *
     */
    protected $_labels = null;

    /**
     *
     */
    protected $_options = null;

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('aw_customerattributes/attribute');
    }

    /**
     * @param string $attributeCode
     *
     * @return AW_Customerattributes_Model_Attribute
     */
    public function loadByCode($attributeCode)
    {
        return $this->load($attributeCode, 'code');
    }

    /**
     * @return mixed
     */
    public function getLabelCollection()
    {
        return Mage::getResourceModel('aw_customerattributes/label_collection')->addAttributeFilter($this);
    }

    /**
     * @return AW_Customerattributes_Model_Resource_Option_Collection
     */
    public function getOptionCollection()
    {
        return Mage::getResourceModel('aw_customerattributes/option_collection')->addAttributeFilter($this);
    }

    /**
     * @return AW_Customerattributes_Model_Attribute_TypeInterface|null
     */
    public function getTypeModel()
    {
        if (is_null($this->_typeInstance)) {
            $typeCode = $this->getData('type');
            $this->_typeInstance = Mage::getModel('aw_customerattributes/source_type')->getModelByTypeCode($typeCode);
            if (!is_null($this->_typeInstance)) {
                $this->_typeInstance->setAttribute($this);
            }
        }
        return $this->_typeInstance;
    }


    /**
     * @return array
     */
    public function getStoreLabels()
    {
        if (is_null($this->_labels)) {
            $this->_labels = array();
            foreach ($this->getLabelCollection() as $label) {
                $this->_labels[$label->getStoreId()] = $label->getValue();
            }
        }
        return $this->_labels;
    }

    /**
     * @param int $storeId
     *
     * @return null|string
     */
    public function getLabel($storeId = 0)
    {
        $labels = $this->getStoreLabels();
        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        }
        if (isset($labels[0])) {
            return $labels[0];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getStoreOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            foreach ($this->getOptionCollection() as $optionId => $option) {
                $this->_options[$optionId]['sort_order'] = $option->getSortOrder();
                $optionValues = $option->getValueCollection();
                foreach ($optionValues as $value) {
                    $this->_options[$optionId]['label'][$value->getStoreId()] = $value->getValue();
                }
            }
        }
        return $this->_options;
    }

    /**
     * @return AW_Customerattributes_Model_Attribute
     */
    public function unpackData()
    {
        $this->_getResource()->unserializeFields($this);
        $this->_afterLoad();
        return $this;
    }

    /**
     * Processing object after load data
     *
     * @return AW_Customerattributes_Model_Attribute
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getStoreIds())) {
            $this->setStoreIds(explode(',', $this->getStoreIds()));
        }
        if (!is_array($this->getCustomerGroups())) {
            $this->setCustomerGroups(explode(',', $this->getCustomerGroups()));
        }
        if (!is_array($this->getDisplayOn())) {
            $this->setDisplayOn(explode(',', $this->getDisplayOn()));
        }
        return parent::_afterLoad();
    }

    /**
     * Processing object before save data
     *
     * @return AW_Customerattributes_Model_Attribute
     */
    protected function _beforeSave()
    {
        if (is_array($this->getStoreIds())) {
            $this->setStoreIds(implode(',', $this->getStoreIds()));
        }
        if (is_array($this->getCustomerGroups())) {
            $this->setCustomerGroups(implode(',', $this->getCustomerGroups()));
        }
        if (is_array($this->getDisplayOn())) {
            $this->setDisplayOn(implode(',', $this->getDisplayOn()));
        }
        return parent::_beforeSave();
    }

    /**
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _afterDelete()
    {
        parent::_afterDelete();
        $this->getTypeModel()->afterAttributeDelete();
    }
}