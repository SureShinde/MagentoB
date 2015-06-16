<?php
class Bilna_Veritrans_Block_Cancel extends Mage_Core_Block_Template {
	public function _getOrderId() {
        return Mage::getSingleton('checkout/session')->getLastRealOrderId();
    }	

    public function _getContinueShoppingUrl() {
        return Mage::getBaseUrl();
    }
}
