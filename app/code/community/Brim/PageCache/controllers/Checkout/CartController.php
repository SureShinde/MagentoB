<?php

require_once 'Mage/Checkout/controllers/CartController.php';

class Brim_PageCache_Checkout_CartController extends Mage_Checkout_CartController {
    /**
     * Ignore form key when adding products to cart via the cart controller action.
     * @return bool
     */
    protected function _validateFormKey()
    {
        return true;
    }
}