<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Shopby_Block_Catalog_Layer_Filter_Stock extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amshopby/attribute.phtml');
        $this->_filterModelName = 'amshopby/catalog_layer_filter_stock';
    }
    
    /**
     * Get Sort Order for filter
     * @return number
     */
    public function getPosition()
    {
    	return Mage::getStoreConfig('amshopby/general/stock_filter_pos');
    }
    
    public function getDisplayType()
    {
    	return 0;
    }
    
	public function getItemsAsArray()
    {
        $items = array(); 
        foreach (parent::getItems() as $itemObject){
        	
            $item = array();
            $item['url']   = $this->htmlEscape($itemObject->getUrl());
            $item['label'] = $itemObject->getLabel();
            $item['count'] = '';
         	$item['countValue']  = $itemObject->getCount();
            if (!$this->getHideCounts()) {
                $item['count']  = ' (' . $itemObject->getCount() . ')';
            }
            

            $item['css'] = 'amshopby-attr';
            if (in_array($this->getDisplayType(), array(1,3))) //dropdown and images
                $item['css'] = '';

            if ($itemObject->getValue() == $this->getRequestValue()){
                $item['css'] .= '-selected';
                if (3 == $this->getDisplayType()) //dropdown
                    $item['css'] = 'selected';
            }

            if ($this->getSeoRel()){ 
                $item['css'] .= '" rel="nofollow';  
            }            
            
            $items[] = $item;
        }
        
        return $items;
    }
    
	public function getRequestValue()
    {
        return Mage::app()->getRequest()->getParam('stock');
    }
}
