<?php

/**
 * Add Free Product to Cart API Resource
*
* @category   Bilna
* @package    Bilna_Paymethod <AW_Afptc>
* @author     Bilna Development Team <core@magentocommerce.com>
*/
class AW_Afptc_Model_Api2_Resource extends Mage_Api2_Model_Resource
{
	/**
	 * Retrieves quote by quote identifier and store code or by quote identifier
	 *
	 * @param int $quoteId
	 * @param string|int $store
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote($quoteId, $storeId = 1)
	{
		/** @var $quote Mage_Sales_Model_Quote */
		$quote = Mage::getModel("sales/quote");
	
		if (!(is_string($storeId) || is_integer($storeId))) {
			$quote->loadByIdWithoutStore($quoteId);
		} else {
			$quote->setStoreId($storeId)
			->load($quoteId);
		}
		if (is_null($quote->getId())) {
			Mage::throwException("Quote Not Exists");
		}
	
		return $quote;
	}
}