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
}
