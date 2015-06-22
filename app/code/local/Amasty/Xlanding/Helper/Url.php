<?php

class Amasty_Xlanding_Helper_Url extends Mage_Core_Helper_Abstract
{
	public function getLandingUrl($query = null, $exclude = array())
	{
		if (!$query) {
			$query = Mage::app()->getRequest()->getParams();
		}
		
		$query = array_merge(Mage::app()->getRequest()->getParams(), $query);
		
		foreach ($exclude as $excludeKey) {
			if (isset($query[$excludeKey])) {
				unset($query[$excludeKey]);
			}
		}
		unset($query['page_id']);
		unset($query['am_landing']);
		
        $key = Mage::app()->getRequest()->getParam('am_landing');
        if (count($query) > 0) {
        	return Mage::getBaseUrl() . $key . '?' . http_build_query($query);
        } else {
        	return Mage::getBaseUrl() . $key;
        }
	}
	
	public function getClearUrl()
	{
		$key = Mage::app()->getRequest()->getParam('am_landing');
       	return Mage::getBaseUrl() . $key;
	}
}
