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
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Model_Mysql4_Blocks_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('awfeatured/blocks');
    }

    protected function _afterLoad()
    {
        foreach ($this->getItems() as $_item) {
            $_item->setStore(@explode(',', $_item->getStore()));
        }
    }

    /**
     * Wrapper for Enterprise admin limitations
     * @param array $stores
     * @return $this
     */
    public function addStoreFilter($stores)
    {
        $this->setStoreFilter($stores);
        return $this;
    }

    /**
     * Filters collection by store ids
     * @param $stores
     * @return AW_Featured_Model_Mysql4_Blocks_Collection
     */
    public function setStoreFilter($stores = null, $breakOnAllStores = false)
    {
        $_stores = array(Mage::app()->getStore()->getId());
        if (is_string($stores)) {
            $_stores = explode(',', $stores);
        }
        if (is_array($stores)) {
            $_stores = $stores;
        }
        if (!in_array('0', $_stores)) {
            array_push($_stores, '0');
        }
        if ($breakOnAllStores && $_stores == array(0)) {
            return $this;
        }
        $_sqlString = '(';
        $i = 0;
        foreach ($_stores as $_store) {
            $_sqlString .= sprintf('find_in_set(%s, store)', $this->getConnection()->quote($_store));
            if (++$i < count($_stores)) {
                $_sqlString .= ' OR ';
            }
        }
        $_sqlString .= ')';
        $this->getSelect()->where($_sqlString);
        return $this;
    }

    public function setBlockIdFilter($blockId)
    {
        $this->getSelect()->where('block_id = ?', $blockId);
        return $this;
    }

    public function setPositionFilter($position)
    {
        $this->getSelect()->where('autoposition = ?', $position);
        return $this;
    }

    public function setIdNotFilter($id)
    {
        if ($id) {
            $this->getSelect()->where('id != ?', $id);
        }
        return $this;
    }

    public function setEnabledFilter($enabled = true)
    {
        $this->getSelect()->where('is_active = ?', intval($enabled));
        return $this;
    }

    /**
     * Covers bug in Magento function
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        return $countSelect->reset()->from($this->getSelect(), array())->columns('COUNT(*)');
    }
}
