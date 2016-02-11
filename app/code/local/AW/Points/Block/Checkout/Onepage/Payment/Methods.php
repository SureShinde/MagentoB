<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Points
 * @version    1.6.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Checkout_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods {

    protected $_summaryForCustomer;

    protected function _toHtml() {
        $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_14;
        if (Mage::helper('points')->magentoLess14())
            $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_13;
        $this->setTemplate('aw_points/checkout/onepage/payment/' . $magentoVersionTag . '/methods.phtml');

        return parent::_toHtml();
    }

    public function getSummaryForCustomer() {
        if (!$this->_summaryForCustomer) {
            $this->_summaryForCustomer = Mage::getModel('points/summary')->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer());
        }
        return $this->_summaryForCustomer;
    }

    public function getMoneyForPoints() {
        if (!$this->getData('money_for_points')) {
            try {
                $moneyForPoints = Mage::getModel('points/rate')
                        ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                        ->exchange($this->getSummaryForCustomer()->getPoints());
                $this->setData('money_for_points', Mage::app()->getStore()->convertPrice($moneyForPoints, true));
            } catch (Exception $ex) {
                
            }
        }
        return $this->getData('money_for_points');
    } 

    public function getNeededPoints() {
        return Mage::helper('points')->getNeededPoints($this->getQuote()->getData('base_subtotal_with_discount'));
    }

    public function getLimitedPoints() {

        $sum = $this->getQuote()->getData('base_subtotal_with_discount');

        return Mage::helper('points')->getLimitedPoints($sum);
    }

    public function getBaseGrandTotalInPoints() {
        return Mage::helper('points')->getNeededPoints($this->getQuote()->getBaseGrandTotal());
    }

    public function pointsSectionAvailable() {
        $isAvailable =
                $this->getSummaryForCustomer()->getPoints()
                && $this->getMoneyForPoints()
                && Mage::helper('points')->isAvailableToRedeem($this->getSummaryForCustomer()->getPoints())
                && $this->customerIsRegistered();
        ;
        if (!Mage::helper('points/config')->getCanUseWithCoupon()) {
            $isAvailable = $isAvailable && !$this->getQuote()->getData('coupon_code');
        }
        return $isAvailable;
    }

    protected function customerIsRegistered() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return $customer->getId() > 0;
    }
    
    public function getMethodLabelAfterHtmlExtend($_code) {
        if ($form = $this->getChild('payment.method.' . $_code)) {
            return $form->getMethodLabelAfterHtml();
        }
    }
    
    public function getPaymentMethodsFilter() {
        $_methods = $this->getMethods();
        $_methodsAllow = $this->getPaymentMethodsByShippingMethod();
        $_result = array ();
        
        foreach ($_methods as $_method) {
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
            
            $_result[] = array (
                'code' => $_code,
                'title' => $_title
            );
        }
        
        return $_result;
    }
    
    public function getPaymentMethodsByShippingMethod() {
        $order = $this->getRequest()->getPost();
        $postData = array (
            'shipping_text' => isset($order['shipping_text']) ? $order['shipping_text'] : '',
            'shipping_type' => isset($order['shipping_type']) ? $order['shipping_type'] : ''
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
