<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Xlanding_Model_Catalog_Layer_Filter_Category extends Amasty_Xlanding_Model_Catalog_Layer_Filter_Category_Pure
{
    protected function _initItems()
    {
    	if (!$key = Mage::app()->getRequest()->getParam('am_landing')) {
    		return parent::_initItems();
    	}
    	
        $data  = $this->_getItemsData();
        $items = array();
        foreach ($data as $itemData) {
            if (!$itemData)
                continue;
                
            $obj = new Varien_Object();
            $obj->setData($itemData);
            if (isset($itemData['id'])) {
            	
            	/*
            	 * Navigation works here
            	 */
            	$url = Mage::helper('amshopby/url')->getFullUrl();
            	if (strpos($url, '?') !== false) {
            		$url .= '&cat=' . $itemData['id'];
            	} else {
            		$url .= '?cat=' . $itemData['id'];
            	}
            } else {
            	$url = Mage::helper('amlanding/url')->getLandingUrl(array('cat' => $itemData['value']));
            }
            $obj->setUrl($url);

            $items[] = $obj;
        }
        $this->_items = $items;
        return $this;
    }
}