<?php

/**
 * Class containing related observers to be run while placing order
 * Created by Bilna Development Team ( development@bilna.com )
 * Date: 31/03/16
 * Time: 14:44
 */
class Bilna_Crossborder_Model_Order
{

    /**
     * Function to validate cross border items based on configuration limit (weight, volume, quantity, and subtotal)
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function validateCrossBorder(Varien_Event_Observer $observer)
    {
        if ($this->isCrossBorderEnabled()) {
            $invalidCount = 0;
            $messages = array();
            $totalWeight = 0;
            $totalVolume = 0;
            $totalQty = 0;
            $subtotal = 0;
            $maxWeightAllowed = (float) $this->getMaxWeightAllowed();
            $maxVolumeAllowed = (float) $this->getMaxVolumeAllowed();
            $maxQtyAllowed = (int) $this->getMaxQtyAllowed();
            $maxSubtotalAllowed = (float) $this->getMaxSubtotalAllowed();

            // Get All Cross Border Items and calculate the totals
            $cartItems = Mage::getModel("checkout/cart")->getItems();
            foreach ($cartItems as $item) {
                $product = $item->getProduct()->load();
                if ($product->getCrossBorder() == 1) {
                    $totalWeight += $item->weight * $item->qty;
                    $totalVolume += ((float) $product->volume_weight ) * $item->qty;
                    $totalQty += $item->qty;
                    $subtotal += ($item->price * $item->qty) - $item->discount_amount;
                    break;
                }
            }

            // Check Weight Limitation
            if ($totalWeight > $maxWeightAllowed) {
                $messages[] = 'max weight exceeded';
                $invalidCount++;
            }

            // Check Volume Limitation
            if ($totalVolume > $maxVolumeAllowed) {
                $messages[] = 'max volume exceeded';
                $invalidCount++;
            }

            // Check Quantity Limitation
            if ($totalQty > (int) $maxQtyAllowed) {
                $messages[] = 'max qty exceeded';
                $invalidCount++;
            }

            // Check Subtotal Limitation
            if ($subtotal > (float) $maxSubtotalAllowed) {
                $messages[] = 'max subtotal exceeded';
                $invalidCount++;
            }

            if ($invalidCount > 0) { // If there is any invalid criteria, throw the Exception
                Mage::throwException(Mage::helper('checkout')->__('Cross Border: ' . implode(', ', $messages)));
            }
        }

        return $this;
    }

    /**
     * Function to check if Cross Border is enabled on the System Configuration
     * @return bool
     */
    public function isCrossBorderEnabled()
    {
        $config = Mage::getStoreConfig('bilna_crossborder/status/enabled');
        if ($config) {
            return true;
        }

        return false;
    }

    public function getMaxWeightAllowed()
    {
        return Mage::getStoreConfig('bilna_crossborder/configuration/max_weight_allowed');
    }

    public function getMaxVolumeAllowed()
    {
        return Mage::getStoreConfig('bilna_crossborder/configuration/max_volume_allowed');
    }

    public function getMaxQtyAllowed()
    {
        return Mage::getStoreConfig('bilna_crossborder/configuration/max_qty_allowed');
    }

    public function getMaxSubtotalAllowed()
    {
        return Mage::getStoreConfig('bilna_crossborder/configuration/max_subtotal_allowed');
    }
}