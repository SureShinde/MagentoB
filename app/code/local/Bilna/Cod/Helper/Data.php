<?php
/**
 * Description of Bilna_Cod_Helper_Data
 *
 * @author  Bilna Development Team
 * @email   development@bilna.com
 */

class Bilna_Cod_Helper_Data extends Mage_Core_Helper_Abstract {
    public function showCodMethod($from = null, $quote = null)
	{
		// check whether the COD is active or not
		if (!$this->isCodEnabled())
			return false;

		if (!$this->checkCodMethodFromCartItems($from, $quote))
			return false;

		return true;
	}

    /* show whether all the items have available COD shipping method */
    private function checkCodMethodFromCartItems($from = null, $quote = null)
    {
        $showCod = true;

        // if the quote param is null, we use the one with shopping cart model
        if (is_null($quote))
        {
        	// if the source is from admin
        	if ($from == 'admin')
        		$cartItems = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getAllItems();
        	else // otherwise
        		$cartItems = Mage::getModel("checkout/cart")->getItems();
        }
        else
        	$cartItems = $quote->getAllItems();

        foreach($cartItems as $item) {
            
            // ignore the parent ( configurable or bundle ), we only check the simple
            if ($item->getProductType() == 'configurable' || $item->getProductType() == 'bundle')
                continue;

            $product = $item->getProduct()->load();
            if ( is_null($product->getCod()) || $product->getCod() == 0 ) {
                $showCod = false;
                break;
            }
        }

        return $showCod;
    }

    // for API
    public function updateParentQuoteCod($quoteData)
    {
        $parents = array();

        for ($i = (count($quoteData['quote_items']) - 1) ; $i >= 0 ; $i--)
        {
            $parentItemId = $quoteData['quote_items'][$i]['parent_item_id'];
            $itemId = $quoteData['quote_items'][$i]['item_id'];
            $isCod = ( is_null($quoteData['quote_items'[$i]['cod']]) ? 0 : $quoteData['quote_items'][$i]['cod'] );

            // if parent does not exist
            if (is_null($parentItemId) || $parentItemId == '')
            {
                $finalIsCod = $this->checkParentCod($itemId, $parents, $isCod);
                $quoteData['quote_items'][$i]['cod'] = $finalIsCod;
            }
            // if parent exists
            else
            {
                $parents[$parentItemId]['cod'][] = $isCod;
            }
        }
        
        return $quoteData;
    }

    private function checkParentCod($itemId, $parents, $isCod)
    {
        if (is_null($parents[$itemId]))
            return $isCod;
        else
        {
            for ($i = 0 ; $i < count($parents[$itemId]['cod']) ; $i++)
            {
                if ($parents[$itemId]['cod'][$i] == 0)
                    return 0;
            }
        }

        return 1;
    }

    /* check whether cod enabled or not */
    public function isCodEnabled()
    {
        $config = Mage::getStoreConfig('payment/cod/active');
        if ($config) {
            return true;
        }

        return false;
    }
}
