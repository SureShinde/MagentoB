<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Model_Mysql4_Image_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('awislider/image');
    }

    public function sortBySortOrder($desc = false) {
        $this->getSelect()->order('sort_order '.($desc ? 'desc' : 'asc'));
        return $this;
    }

    public function addActualDateFilter() {
        $this->getSelect()->where('(active_from IS NULL OR active_from <= ?) AND (active_to IS NULL OR active_to >= ?)', now(true), now(true));
        return $this;
    }
    
    public function addSliderFilter($sliderId) {
        $this->getSelect()->where('pid = ?', $sliderId);
        return $this;
    }
    
    /**
     * Filters collection by store ids
     * @param $stores
     * @return AW_Islider_Model_Mysql4_Image_Collection
     */
    public function addStoreFilter($stores = null, $breakOnAllStores = false) {
        $_stores = array(Mage::app()->getStore()->getId());
        if(is_string($stores)) $_stores = explode(',', $stores);
        if(is_array($stores)) $_stores = $stores;
        if(!in_array('0', $_stores))
            array_push($_stores, '0');
        if($breakOnAllStores && $_stores == array(0)) return $this;
        $_sqlString = '(';
        $i = 0;
        foreach($_stores as $_store) {
            $_sqlString .= sprintf('find_in_set(%s, store)', $this->getConnection()->quote($_store));
            if(++$i < count($_stores))
                $_sqlString .= ' OR ';
        }
        $_sqlString .= ')';
        $this->getSelect()->where($_sqlString);

        return $this;
    }

    public function addActiveFilter($active = true) {
        $this->getSelect()->where('is_active = ?', $active ? 1 : 0);
        return $this;
    }
}
