<?php

/**
 * API2 class for rest (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
abstract class Bilna_Checkout_Model_Api2_Customer_Rest extends Bilna_Checkout_Model_Api2_Customer
{
	/**
     * Customer address types
     */
    const ADDRESS_BILLING    = Mage_Sales_Model_Quote_Address::TYPE_BILLING;
    const ADDRESS_SHIPPING   = Mage_Sales_Model_Quote_Address::TYPE_SHIPPING;

    /**
     * Customer checkout types
     */
     const MODE_CUSTOMER = Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER;
     const MODE_REGISTER = Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER;
     const MODE_GUEST    = Mage_Checkout_Model_Type_Onepage::METHOD_GUEST;
}