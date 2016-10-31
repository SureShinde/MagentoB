<?php

/**
 * API2 class for payment (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Payment_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Shipping_Rest
{
	protected $quoteId = '';
    protected $storeId = 1;

    /**
     * Set an Shipping Method for Shopping Cart
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    protected function _retrieve()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $storeId = 1;

        $this->quoteId = $quoteId;
        $this->storeId = $storeId;

        try {
            //$quote = $this->_getQuote($quoteId, $storeId);
            
            $_methods = $this->_getMethods();
            $_methodsAllow = $this->getPaymentMethodsByShippingMethodUpdated();
            $_result = array ();

            foreach ($_methods as $_method)
            {
                $_code = $_method->getCode();
                $_title = $_method->getTitle();

                if ($this->getPaymentCodStatus() == 1) {
                    // check payment method allow
                    if (is_array($_methodsAllow)) {
                        if (count($_methodsAllow) > 0) {
                            if (!in_array($_code, $_methodsAllow)) {
                                continue;
                            }
                        }
                        else {
                            break;
                        }
                    }
                    else {
                        if ($_methodsAllow != '*') {
                            break;
                        }
                    }
                }

                $_label = 'instructions';
                switch($_code)
                {
                    case 'klikbca':
                    case 'klikpay':
                        $_label = 'message';
                        break;
                }

                $_result[] = array (
                    'code' => $_code,
                    'title' => $_title,
                    'label' => Mage::getStoreConfig('payment/'.$_code.'/'.$_label)
                );
            }

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array("payment_methods" => $_result);
    }

    public function getPaymentCodStatus()
    {
        return Mage::getStoreConfig('payment/cod/active');
    }

    private function _getMethods()
    {
        $quote = $this->getQuote();
        $storeId = $quote->getStoreId();
        $methods = array();
        $payment = Mage::helper('payment')->getStoreMethods($this->_getStore(), $quote);
        foreach ($payment as $method)
        {
            if ($this->_canUseMethod($method) && $method->isApplicableToQuote(
                $quote,
                Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL
            )) {
                $this->_assignMethod($method);
                $methods[] = $method;
            }

        }

        return $methods;
    }

    protected function _assignMethod($method)
    {
        $method->setInfoInstance($this->getQuote()->getPayment());
        return $this;
    }
    
    protected function _validateCOD($selectedShippingMethod = null)
    {
        $validateArray = [
            'Free Shipping', 
            'Bayar di Tempat', 
            'Standard Shipping',
            'Express Shipping',
            'Economy Shipping',
            'International Shipping'
        ];
        if(in_array($selectedShippingMethod, $validateArray)) {
            return TRUE;
        }
        
        return FALSE;
    }

    protected function getPaymentMethodsByShippingMethodUpdated() {
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $shippingDescription = $shippingAddress->getShippingDescription();

        $mapper = [
            'standard' => 1,
            'free shipping' => 1,
            'economy' => 1,
            'international' => 1,
            'bayar di tempat' => 2,
            'cod' => 2,
            'express' => 3
        ];

        if (strpos(strtolower($shippingDescription), 'standard') !== false)
            $shippingType = $mapper['standard'];
        else
        if (strpos(strtolower($shippingDescription), 'free shipping') !== false)
            $shippingType = $mapper['free shipping'];
        else
        if (strpos(strtolower($shippingDescription), 'economy') !== false)
            $shippingType = $mapper['economy'];
        else
        if (strpos(strtolower($shippingDescription), 'international') !== false)
            $shippingType = $mapper['international'];
        else
        if (strpos(strtolower($shippingDescription), 'bayar di tempat') !== false)
            $shippingType = $mapper['bayar di tempat'];
        else
        if (strpos(strtolower($shippingDescription), 'cod') !== false)
            $shippingType = $mapper['cod'];
        else
        if (strpos(strtolower($shippingDescription), 'express') !== false)
            $shippingType = $mapper['express'];
        else
        if ($quote->getIsWholesale()) {
            $shippingType = 4; // this is for wholesale shipping method in payment_base_shipping table and it will exclude COD paymethod
        }

        if ($shippingType == $mapper['express']) { // if this is express shipping
            $allowedPaymethod = explode(',', Mage::getStoreConfig('bilna_expressshipping/paymethod/allowed_paymethod'));
        }

        $shipData = array (
            'shipping_text' => $shippingAddress->getShippingDescription(),
            'shipping_type' => $shippingType 
        );

        $paymentMethodsArr = Mage::getModel('cod/paymentMethod')->getSupportPaymentMethodsByShippingMethod($shipData);
        $result = array ();

        if (is_array($paymentMethodsArr)) {
            if (count($paymentMethodsArr) > 0) {
                foreach ($paymentMethodsArr as $key => $value) {
                    if (isset($allowedPaymethod)) {
                        if (in_array($value, $allowedPaymethod)) {
                            $result[] = $value;
                        }
                    }
                    else {
                        if ($value == '*') {
                            $result = $value;
                            break;
                        }
                        else {
                            $result[] = $value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getPaymentMethodsByShippingMethod()
    {
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $shipData = array (
            'shipping_text' => $shippingAddress->getShippingDescription(),
            'shipping_type' => $shippingAddress->getShippingMethod()
        );
        $paymentMethodsArr = Mage::getModel('cod/paymentMethod')->getSupportPaymentMethodsByShippingMethod($shipData);
        $result = array ();
        if (is_array($paymentMethodsArr)) {
            if (count($paymentMethodsArr) > 0) {
                foreach ($paymentMethodsArr as $key => $value) {
                    if ($value == '*') {
                        $result = $value;
                        break;
                    }
                    else {
                        $result[] = $value;
                    }
                }
                
                if(!empty($result)) {
                    $newResult = [];
                    
                    foreach($result as $key => $item) { 
                        if(substr_count($shipData['shipping_text'], 'Free Shipping') || substr_count($shipData['shipping_text'], 'Standard Shipping')) {
                            $newResult = $this->_removeKey('cod', $result);
                            break;
                        } elseif(substr_count($shipData['shipping_text'], 'Bayar di Tempat')) {
                            $newResult = ['cod'];
                            break;
                        }
                    }
                }                
            }
        }

        return (empty($newResult) ? $result : $newResult);
    }
    
    protected function _removeKey($removedKey, $array = array())
    {
        $indexRemoved = array_search($removedKey, $array);
        unset($array[$indexRemoved]);
        return $array;
    }

    protected function getQuote()
    {
        $quote = $this->_getQuote($this->quoteId, $this->storeId);

        return $quote;
    }

    protected function _canUseMethod($method)
    {
        return $method->isApplicableToQuote($this->getQuote(), Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
            | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
        );
    }

    /**
     * Set an Shipping Method for Shopping Cart
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    protected function _retrieveOld()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $storeId = 1;

        try {
            $quote = $this->_getQuote($quoteId, $storeId);
            $store = $quote->getStoreId();

            $total = $quote->getBaseSubtotal();

            $methodsResult = array();
            $methods = Mage::helper('payment')->getStoreMethods($store, $quote);

            foreach ($methods as $method)
            {
                /** @var $method Mage_Payment_Model_Method_Abstract */
                if ($this->_canUsePaymentMethod($method, $quote)) {
                    $isRecurring = $quote->hasRecurringItems() && $method->canManageRecurringProfiles();

                    if ($total != 0 || $method->getCode() == 'free' || $isRecurring) {
                        $methodsResult[] = array(
                            'code' => $method->getCode(),
                            'title' => $method->getTitle(),
                            'cc_types' => $this->_getPaymentMethodAvailableCcTypes($method),
                        );
                    }
                }
            }

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array("payment_methods" => $methodsResult);
    }

    /**
     * @param  $method
     * @param  $quote
     * @return bool
     */
    protected function _canUsePaymentMethod($method, $quote)
    {
        if (!($method->isGateway() || $method->canUseInternal())) {
            return false;
        }

        if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency(Mage::app()->getStore($quote->getStoreId())->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $quote->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }

        return true;
    }

    protected function _getPaymentMethodAvailableCcTypes($method)
    {
        $ccTypes = Mage::getSingleton('payment/config')->getCcTypes();
        $methodCcTypes = explode(',', $method->getConfigData('cctypes'));
        foreach ($ccTypes as $code => $title) {
            if (!in_array($code, $methodCcTypes)) {
                unset($ccTypes[$code]);
            }
        }
        if (empty($ccTypes)) {
            return null;
        }

        return $ccTypes;
    }
}
