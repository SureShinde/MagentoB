<?php
/**
 * Description of Bilna_Paymethod_Block_Checkout_Cart_Totals
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Cart_Totals extends Mage_Checkout_Block_Cart_Totals {
    public function getBaseGrandtotal() {
        $firstTotal = reset($this->_totals);
        
        if ($firstTotal) {
            return $firstTotal->getAddress()->getBaseGrandTotal();
        }
        
        return 0;
    }
}
