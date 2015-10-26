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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Afptc_Model_Resource_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('awafptc/rule');
    }
    
    
    public function addStatusFilter()
    {
        $this->getSelect()->where('main_table.status = ?', 1);

        return $this;
    }
    
    public function excludePopups()
    {
        $this->getSelect()->where('main_table.show_popup = ?', 0);

        return $this; 
    }
    
    public function addHasProductFilter()
    {
        $this->getSelect()->where('main_table.product_id IS NOT NULL');

        return $this;
    }
    
    public function addPriorityOrder()
    {
        $this->getSelect()->order('main_table.priority DESC');

        return $this;
    }
    
    public function addTimeLimitFilter()
    {
        $this->getSelect()
                ->where("if(main_table.end_date is null, true, main_table.end_date > UTC_TIMESTAMP()) AND 
                    if(main_table.start_date is null, true, main_table.start_date < UTC_TIMESTAMP())");

        return $this;
    }
    
    public function addStoreFilter($store)
    {
        $this->getSelect()->where('find_in_set(0, store_ids) OR find_in_set(?, store_ids)', $store);

        return $this;
    }
    
    public function addGroupFilter($group)
    {
        $this->getSelect()->where('find_in_set(?, customer_groups)', $group);

        return $this;
    }
    
    public function addProductFilter($products)
    {
        $products = (array) $products;

        $this->getSelect()->where('main_table.product_id IN (?)', $products);

        return $this;
    }
    
    public function joinProductStock($website)
    {        
        $this->getSelect()->join(array('stock_table' => $this->getTable('cataloginventory/stock_status')), 
                'main_table.product_id = stock_table.product_id', array())
                ->where('stock_table.stock_status = 1')
                ->where('stock_table.website_id = ?', $website);
       
        return $this;        
    }
    
    public function joinProductWebsite($website)
    {        
        $this->getSelect()->join(array('website_table' => $this->getTable('catalog/product_website')), 
                'main_table.product_id = website_table.product_id', array())
                ->where('website_table.website_id = ?', $website);
        
        return $this;
    }
    
    public function joinProductStatus($store)
    {
        $status = Mage::getModel('eav/entity')->setType('catalog_product')->getAttribute('status');
        
        $this->getSelect()->join(array('status_td' => $status->getBackend()->getTable()), 
                "main_table.product_id = status_td.entity_id AND status_td.attribute_id = {$status->getAttributeId()} AND
                 status_td.store_id = 0 AND value = 1", array());
                 
            return $this;
    }
    
    
 
}
