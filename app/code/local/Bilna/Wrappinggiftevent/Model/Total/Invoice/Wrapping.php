<?php
/**
 *
 * @category   Bilna
 * @package    Bilna_Wrappinggiftevent
 * @version    
 * @copyright  Copyright (c) 
 * @license    
 */


class Bilna_Wrappinggiftevent_Model_Total_Invoice_Wrapping extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{ 
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    { 
        $order = $invoice->getOrder();
		
		$wrappingPrice = $order->getWrappingPrice();
		$baseWrappingPrice = $order->getWrappingPrice();

		$invoice->setGrandTotal($invoice->getGrandTotal() + $wrappingPrice);
		$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseWrappingPrice);
			
		$invoice->setWrappingPrice($wrappingPrice);
		$invoice->setBaseWrappingPrice($baseWrappingPrice);

		return $this;
    }
}