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

class AW_Customerattributes_Block_Adminhtml_Customer_Attribute_Edit_Tab_Details
    extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Manage Labels and Options');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Manage Labels and Options');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve frontend labels of attribute for each store
     *
     * @return array
     */
    public function getLabelValues()
    {
        $values = array();
        $storeLabels = $this->getAttributeObject()->getStoreLabels();
        foreach ($this->getStores() as $store) {
            $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
        }
        return $values;
    }

    /**
     * Retrieve attribute option values if attribute input type select or multiselect
     *
     * @return array
     */
    public function getOptionValues()
    {
        $attributeType = $this->getAttributeObject()->getType();
        $defaultValues = $this->getAttributeObject()->getDefaultValue();

        if ($attributeType == 'dropdown' || $attributeType == 'multipleselect') {
            $defaultValues = explode(',', $defaultValues);
        } else {
            $defaultValues = array();
        }

        switch ($attributeType) {
            case 'dropdown':
                $inputType = 'radio';
                break;
            case 'multipleselect':
                $inputType = 'checkbox';
                break;
            default:
                $inputType = '';
                break;
        }

        $optionValues = array();
        foreach ($this->getAttributeObject()->getStoreOptions() as $optionValueId => $optionValue) {
            $value = array();
            if (in_array($optionValueId, $defaultValues)) {
                $value['checked'] = 'checked="checked"';
            } else {
                $value['checked'] = '';
            }
            $value['intype'] = $inputType;
            $value['id'] = $optionValueId;
            $value['sort_order'] = $optionValue['sort_order'];
            foreach ($this->getStores() as $store) {
                if (isset($optionValue['label'][$store->getId()])) {
                    $value['store' . $store->getId()] = htmlspecialchars($optionValue['label'][$store->getId()]);
                } else {
                    $value['store' . $store->getId()] = '';
                }
            }
            $optionValues[] = new Varien_Object($value);
        }
        return $optionValues;
    }


    /**
     * Retrieve attribute object from registry
     *
     * @return AW_Customerattributes_Model_Attribute
     */
    public function getAttributeObject()
    {
        return Mage::registry('current_attribute');
    }
}