<?php

/**
 * Class containing related observers to be run while placing order
 * @author Bilna Development Team <development@bilna.com>
 * Date: 31/03/16
 * Time: 14:44
 */
class Bilna_Crossborder_Model_CrossBorder
{

    /**
     * Observer to validate cross border items based on configuration limit (weight, volume, quantity, and subtotal)
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function validateSaveOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethod();

        // Check if the 3D secure is enabled for that payment method
        $threeDSecure = Mage::getStoreConfig('payment/' . $paymentMethod . '/threedsecure');

        // If the payment don't have 3D secure or 3D secure is not enabled
        if (is_null($threeDSecure) || $threeDSecure != "1") {
            $validationResult = $this->validate();
            if (!$validationResult['success']) {
                Mage::throwException($validationResult['message']);
            }
        }

        return $this;
    }

    /**
     * Function to validate cross border items based on configuration limit (weight, volume, quantity, and subtotal)
     * @return array
     */
    public function validate()
    {
        $success = true;
        $message = '';

        $crossBorderHelper = Mage::helper('bilna_crossborder');
        if ($crossBorderHelper->isCrossBorderEnabled()) {
            $invalidCount = 0;
            $messages = array();
            $totalWeight = 0;
            $totalVolume = 0;
            $subtotal = 0;
            $crossBorderConfig = $crossBorderHelper->getConfiguration();
            $maxWeightAllowed = $crossBorderConfig['max_weight_allowed'];
//            $maxVolumeAllowed = $crossBorderConfig['max_volume_allowed'];
            $maxSubtotalAllowed = $crossBorderConfig['max_subtotal_allowed'];

            // Get All Cross Border Items and calculate the totals
            $cartItems = Mage::helper("checkout/cart")->getQuote()->getAllItems();
            if (!empty($cartItems)) {
                foreach ($cartItems as $item) {
                    if ($item->cross_border == 1) {
                        $totalWeight += $item->weight * $item->qty;
    //                    $totalVolume += ((float) $item->volume_weight ) * $item->qty;
                        $subtotal += ($item->price * $item->qty) - $item->discount_amount;
                    }
                }
            }

            // Check Weight Limitation
            if ($totalWeight > $maxWeightAllowed) {
                $messages[] = Mage::helper('checkout')->__('Total berat produk impor melebihi ' . $maxWeightAllowed . ' kg');
                $invalidCount++;
            }

            // Check Volume Limitation
            /*if ($totalVolume > $maxVolumeAllowed) {
                $messages[] = Mage::helper('checkout')->__('Volume pesanan produk impor lebih dari ') . $maxVolumeAllowed;
                $invalidCount++;
            }*/

            // Check Subtotal Limitation
            if ($subtotal > (float) $maxSubtotalAllowed) {
                $messages[] = Mage::helper('checkout')->__('Total harga pesanan produk impor melebihi Rp ') . $maxSubtotalAllowed;
                $invalidCount++;
            }

            if ($invalidCount > 0) { // If there is any invalid criteria, throw the Exception
                $message = Mage::helper('checkout')->__('CrossBorder:') . implode(', ', $messages);
                $success = false;
            }
        } else { // If Cross Border is disabled
            $cartItems = Mage::helper("checkout/cart")->getQuote()->getAllItems();
            if (!empty($cartItems)) {
                foreach ($cartItems as $item) {
                    // If there is any cross border item on shopping cart
                    if ($item->cross_border == 1) {
                        $message = Mage::helper('checkout')->__('CrossBorder:') .
                            Mage::helper('checkout')->__('Layanan pengiriman produk impor sedang tidak tersedia. Hapus produk impor untuk melanjutkan pesanan');
                        $success = false;
                        break;
                    }
                }
            }
        }

        return array('success' => $success, 'message' => $message);
    }
}