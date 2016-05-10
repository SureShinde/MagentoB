<?php
/**
 * Description of Bilna_Crossborder_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Crossborder_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Helper to check whether a product is cross border product or not
     * @param $product
     * @return boolean
     */
    public function isCrossBorder($product)
    {
        $isCrossBorder = false;
        if ($product instanceOf Mage_Catalog_Model_Product) { // for product list and detail
            if ($product->getData('cross_border') == 1) {
                $isCrossBorder = true;
            }
        } elseif ($product instanceOf Mage_Sales_Model_Quote_Item) { // for checkout/cart
            if ($product->getCrossBorder() == 1) {
                $isCrossBorder = true;
            }
        }
        return $isCrossBorder;
    }

    public function validateAddToCart($product, $qty = 1, $cart)
    {
        $crossBorderError = 0;

        if (($product->getData('cross_border')) && ($this->isCrossBorderEnabled())) {
            $quoteId = $cart->getQuote()->getId();
            $crossBorderConfig = $this->getConfiguration();
            $maxVolume = $crossBorderConfig['max_volume_allowed'];
            $maxWeight = $crossBorderConfig['max_weight_allowed'];
            $maxSubtotal = $crossBorderConfig['max_subtotal_allowed'];
            $volumeWeight = (float)$product->getData('volume_weight');
            $weight = (float)$product->getData('weight');
            $price = (int)$product->getData('price');

            if (!is_null($quoteId)) {
                $quoteItemCollection = $cart->getItems()->getData();
                $totalArray = $this->__getTotalStoredCrossBorder($quoteItemCollection);

                if ((($weight * $qty) + $totalArray['weight']) > $maxWeight) {
                    $crossBorderError++;
                    $message = $this->__('Berat pesanan produk impor lebih dari ' . $maxWeight . ' kg');
                    Mage::throwException($message);
                }

                if ((($price * $qty) + $totalArray['subtotal']) > $maxSubtotal) {
                    $crossBorderError++;
                    $message = $this->__('Harga total pesanan produk impor lebih dari Rp ' . $maxSubtotal);
                    Mage::throwException($message);
                }
            } else { // if (count($storedCrossBorder) == 0)
                if (($volumeWeight * $qty) > $maxVolume) {
                    $crossBorderError++;
                    $message = $this->__('Volume pesanan produk impor lebih dari ' . $maxVolume);
                    Mage::throwException($message);
                }
                
                if (($weight * $qty) > $maxWeight) {
                    $crossBorderError++;
                    $message = $this->__('Berat pesanan produk impor lebih dari ' . $maxWeight . ' kg');
                    Mage::throwException($message);
                }

                if (($price * $qty) > $maxSubtotal) {
                    $crossBorderError++;
                    $message = $this->__('Harga total pesanan produk impor lebih dari Rp ' . $maxSubtotal);
                    Mage::throwException($message);
                }
            }
        } elseif (($product->getData('cross_border')) && (!$this->isCrossBorderEnabled())) {
            $crossBorderError++;
            $message = $this->__('Layanan pengiriman produk impor sedang tidak tersedia.');
            Mage::throwException($message);
        }

        return $crossBorderError;
    }

    public function validateUpdateShoppingCart($totalArray, $newData, $oldData)
    {
    	$crossBorderConfig = $this->getConfiguration();
        $maxVolume = $crossBorderConfig['max_volume_allowed'];
        $maxWeight = $crossBorderConfig['max_weight_allowed'];
        $maxSubtotal = $crossBorderConfig['max_subtotal_allowed'];
        $crossBorderError = 0;

        if (($oldData['cross_border'] == 1) && $this->isCrossBorderEnabled()) {
            if ((int)$newData[$oldData['item_id']]['qty'] > (int)$oldData['qty']) {
                $qtyDiff = (int)$newData[$oldData['item_id']]['qty'] - (int)$oldData['qty'];

                if ((($qtyDiff * $oldData['weight']) + $totalArray['weight']) > $maxWeight) {
                	$crossBorderError++;
                    $message = $this->__('Berat pesanan produk impor lebih dari ' . $maxWeight . ' kg');
                    Mage::throwException($message);
                }
                if ((($qtyDiff * $oldData['price']) + $totalArray['subtotal']) > $maxSubtotal) {
                	$crossBorderError++;
                    $message = $this->__('Harga total pesanan produk impor lebih dari Rp ' . $maxSubtotal);
                    Mage::throwException($message);
                }
            }
        }
    }

    /**
     * Function to get Cross Border Configuration
     */
    public function getConfiguration()
    {
        $crossBorderConfig = Mage::getStoreConfig('bilna_crossborder/configuration');
        $config = array(
            'max_weight_allowed' => (float) $crossBorderConfig['max_weight_allowed'],
            'max_volume_allowed' => (float) $crossBorderConfig['max_volume_allowed'],
            'max_subtotal_allowed' => (float) $crossBorderConfig['max_subtotal_allowed']
        );
        return $config;
    }

    public function __getTotalStoredCrossBorder($crossBorderSession)
    {
        $totalArray = array(
            'weight' => 0,
            'subtotal' => 0,
            'qty' => 0
        );

        foreach ($crossBorderSession as $quote) {
            if ($quote['cross_border'] == 1) {
                $qty = $quote['qty'];
                foreach ($quote as $quoteAttribute => $quoteValue) {
                    if ($quoteAttribute == 'weight') {
                        $totalArray['weight'] += $quoteValue * $qty;
                    }

                    if ($quoteAttribute == 'price') {
                        $totalArray['subtotal'] += $quoteValue * $qty;
                    }
                }
            }
        }

        return $totalArray;
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

    /**
     * Function to validate cross border items based on configuration limit (weight, volume, quantity, and subtotal)
     * @param $quote
     * @return array
     */
    public function validateQuote($quote)
    {
        $success = true;
        $message = '';

        if ($this->isCrossBorderEnabled()) {
            $invalidCount = 0;
            $messages = array();
            $totalWeight = 0;
            $totalVolume = 0;
            $subtotal = 0;
            $crossBorderConfig = $this->getConfiguration();
            $maxWeightAllowed = $crossBorderConfig['max_weight_allowed'];
//            $maxVolumeAllowed = $crossBorderConfig['max_volume_allowed'];
            $maxSubtotalAllowed = $crossBorderConfig['max_subtotal_allowed'];

            // Get All Cross Border Items and calculate the totals
            $cartItems = $quote->getAllItems();
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
                $success = false;
            }
        } else { // If Cross Border is disabled
            $cartItems = Mage::helper("checkout/cart")->getQuote()->getAllItems();
            if (!empty($cartItems)) {
                foreach ($cartItems as $item) {
                    // If there is any cross border item on shopping cart
                    if ($item->cross_border == 1) {
                        $messages[] =
                            Mage::helper('checkout')->__('Layanan pengiriman produk impor sedang tidak tersedia. Hapus produk impor untuk melanjutkan pesanan');
                        $success = false;
                        break;
                    }
                }
            }
        }

        return array('success' => $success, 'messages' => $messages);
    }
}
