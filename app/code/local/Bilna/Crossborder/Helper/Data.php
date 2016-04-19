<?php
/**
 * Description of Bilna_Crossborder_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Crossborder_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * helper to check if a cart contains cross border item
     * @return bool
     */
    public function hasCrossBorderItem()
    {
        $crossBorderModel = Mage::getModel('bilna_crossborder/CrossBorder');
        return $crossBorderModel->hasCrossBorderItem();
    }

    /**
     * Helper to get html element for cross border product
     * @param $product
     * @return string
     */
    public function getHtmlCrossBorder($product)
    {
        if ($product instanceOf Mage_Catalog_Model_Product) { // for product list and detail
            if ($product->getData('cross_border') == 1) {
                return '<span>import</span>';
            }
        } elseif ($product instanceOf Mage_Sales_Model_Quote_Item) { // for checkout/cart
            if ($product->getCrossBorder() == 1) {
                return '<span>import</span>';
            }
        }
        return '<span>&nbsp;</span>';
    }
}