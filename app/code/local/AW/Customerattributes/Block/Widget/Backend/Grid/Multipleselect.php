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

class AW_Customerattributes_Block_Widget_Backend_Grid_Multipleselect
    extends AW_Customerattributes_Block_Widget_Backend_GridAbstract
{
    public function getColumnId()
    {
        return $this->getProperty('code') ? $this->getProperty('code') : null;
    }

    public function getColumnProperties()
    {
        return array(
            'header'   => $this->_getLabel(),
            'index'    => $this->getColumnId(),
            'sortable' => false,
            'type'     => 'options',
            'options'  => $this->_getOptionsForSelect(),
            'renderer' => 'aw_customerattributes/widget_backend_grid_multipleselect_renderer',
        );
    }

    private function _getOptionsForSelect()
    {
        if (is_null($this->getTypeModel())) {
            return null;
        }
        $attribute = $this->getTypeModel()->getAttribute();
        if (is_null($attribute)) {
            return null;
        }
        $storeId = Mage::app()->getStore()->getId();
        $options = Mage::helper('aw_customerattributes/options')
            ->getOptionsForAttributeByStoreId($attribute, $storeId, false);
        foreach ($options as $key => $value) {
            $options[$key] = htmlspecialchars($value);
        }
        return $options;
    }
}