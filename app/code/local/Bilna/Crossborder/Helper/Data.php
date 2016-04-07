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
}