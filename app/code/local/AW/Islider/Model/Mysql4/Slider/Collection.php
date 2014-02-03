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

class AW_Islider_Model_Mysql4_Slider_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('awislider/slider');
    }
    
    protected function _afterLoad() {
        foreach($this->getItems() as $_item) {
            if(is_string($_item->getData('store')))
                $_item->setData('store', @explode(',', $_item->getData('store')));
        }
    }

    /**
     * Filters collection by store ids
     * @param $stores
     * @return AW_Islider_Model_Mysql4_Slider_Collection
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
    
    public function addIdFilter($id, $exclude = false) {
        if($id != null)
            $this->getSelect()->where('id '.($exclude ? '!=' : '=').' ?', $id);
        return $this;
    }

    public function addBlockIdFilter($blockId, $exclude = false) {
        $this->getSelect()->where('block_id '.($exclude ? '!=' : '=').' ?', $blockId);
        return $this;
    }

    public function addEnabledFilter($enabled = true) {
        $this->getSelect()->where('is_active = ?', $enabled ? 1 : 0);
        return $this;
    }

    public function addPositionFilter($position) {
        $this->getSelect()->where('autoposition = ?', $position);
        return $this;
    }
    
    /**
     * Covers bug in Magento function
     * @return Varien_Db_Select
     */
    public function getSelectCountSql(){
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        return $countSelect->reset()->from($this->getSelect(), array())->columns('COUNT(*)');
    }
}
