<?php
/**
 * Description of Bilna_Paymethod_Block_Checkout_Onepage_Billing
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Onepage_Review_Info extends Mage_Checkout_Block_Onepage_Review_Info {

	private $_localItems = array();
	private $_importItems = array();

    public function getUrl($route = '', $params = array ()) {
        $url = Mage::helper('paymethod')->checkHttpsProtocol($this->_getUrlModel()->getUrl($route, $params));
        
        return $url;
    }

    public function getLocalAndImportItems()
    {
        $quoteItems = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
        if (!empty($quoteItems)) {
        	foreach($quoteItems as $quoteItem) {
        		$product = $quoteItem->getProduct()->load();
        		if ($product->getCrossBorder() == 1) {
        			$this->_importItems[] = $quoteItem;
        		} else {
        			$this->_localItems[] = $quoteItem;
        		}
        	}
        }
        return;
    }

    public function getLocalItems()
    {
    	return $this->_localItems;
    }

    public function getImportItems()
    {
    	return $this->_importItems;
    }
}
