<?php

class Amasty_Xlanding_Model_Rewrite_Price17 extends Amasty_Xlanding_Model_Resource_Catalog_Layer_Filter_Price
{

	public $_maxMinPrice = null;
	
	protected  function _construct()
	{
		parent::_construct();
	}

    /**
	 * Retrieve minimal and maximal prices
	 * 
	 * @param Mage_Catalog_Model_Layer_Filter_Price $filter
	 * @return array (max, min)
	 */
	public function _getMaxMinPrice($filter)
	{
		return Mage::helper('amshopby/price17')->_getMaxMinPrice($filter, $this);
	}
	

    /**
     * Retrieve maximal price
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return float
     */
    public function getMaxPrice($filter)
    {
        return Mage::helper('amshopby/price17')->getMaxPrice($filter, $this);
    }
    
    /**
     * Retrieve maximal price
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return float
     */
	public function getMinPrice($filter)
    {
    	return Mage::helper('amshopby/price17')->getMinPrice($filter, $this);
    }
    
    /**
     * Remove price records from where query
     * 
     * @param Varien_Db_Select $select
     * @param string $priceExpression
     * @return Varien_Db_Select
     */
    public function _removePriceFromSelect($select, $priceExpression)
    {
    	return Mage::helper('amshopby/price17')->_removePriceFromSelect($select, $priceExpression);
    }
    
    /**
     * Enter description here ...
     * @param Varien_Db_Select $select
     * @return string
     */
    public function getPriceExpression($select) 
    {
    	return Mage::helper('amshopby/price17')->getPriceExpression($select);
    }
    
	/**
     * Retrieve array with products counts per price range
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param array $ranges (23=>array(1,100), 24=>101-200)
     * @return array
     */
    public function getFromToCount($filter, $ranges)
    {
    	return Mage::helper('amshopby/price17')->getFromToCount($filter, $ranges, $this);
    }
}
