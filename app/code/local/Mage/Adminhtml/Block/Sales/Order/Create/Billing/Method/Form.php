<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order create payment method form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form extends Mage_Payment_Block_Form_Container {
    /**
     * Check payment method model
     *
     * @param Mage_Payment_Model_Method_Abstract|null $method
     * @return bool
     */
    protected function _canUseMethod($method) {
        return $method && $method->canUseInternal() && parent::_canUseMethod($method);
    }

    /**
     * Check existing of payment methods
     *
     * @return bool
     */
    public function hasMethods() {
        $methods = $this->getMethods();
        
        if (is_array($methods) && count($methods)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get current payment method code or the only available, if there is only one method
     *
     * @return string|false
     */
    public function getSelectedMethodCode() {
        // One available method. Return this method as selected, because no other variant is possible.
        $methods = $this->getMethods();
        
        if (count($methods) == 1) {
            foreach ($methods as $method) {
                return $method->getCode();
            }
        }

        // Several methods. If user has selected some method - then return it.
        $currentMethodCode = $this->getQuote()->getPayment()->getMethod();
        
        if ($currentMethodCode) {
            return $currentMethodCode;
        }

        // Several methods, but no preference for one of them.
        return false;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        return Mage::getSingleton('adminhtml/session_quote')->getQuote();
    }

    /*
    * Whether switch/solo card type available
    */
    public function hasSsCardType() {
        $availableTypes = explode(',', $this->getQuote()->getPayment()->getMethod()->getConfigData('cctypes'));
        $ssPresenations = array_intersect(array ('SS', 'SM', 'SO'), $availableTypes);
        
        if ($availableTypes && count($ssPresenations) > 0) {
            return true;
        }
        
        return false;
    }

    public function getPaymentMethodsFilter() {
        $_methods = $this->getMethods();
        $_methodsReject = $this->getPaymentMethodHide();
        $_methodsAllow = $this->getPaymentMethodsByShippingMethod();
        $_result = array ();
        
        foreach ($_methods as $_method) {
            $_code = $_method->getCode();
            $_title = $_method->getTitle();
            
            // check payment method reject
            if (in_array($_code, $_methodsReject)) {
                continue;
            }
            
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
            
            $_result[] = array (
                'code' => $_code,
                'title' => $_title
            );
        }
        
        return $_result;
    }
    
    private function getPaymentMethodHide() {
        $payment = explode(',', Mage::getStoreConfig('bilna_module/paymethod/payment_hide_admin'));
        
        return $payment;
    }

    public function getPaymentMethodsByShippingMethod() {
        $order = $this->getRequest()->getPost('order');
        $postData = array (
            'shipping_text' => $order['shipping_text'],
            'shipping_type' => $order['shipping_type']
        );
        $paymentMethodsArr = Mage::getModel('cod/paymentMethod')->getSupportPaymentMethodsByShippingMethod($postData);
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
            }
        }
                
        return $result;
    }
    
    public function getPaymentCodStatus() {
        return Mage::getStoreConfig('payment/cod/active');
    }
}
