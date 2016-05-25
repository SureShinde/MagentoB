<?php
/**
 * Description of Bilna_Paymethod_Block_Checkout_Onepage_Success
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Onepage_Success extends Mage_Checkout_Block_Onepage_Success {
    private $_order;

    public function getOrder() {
        $orderId = $this->getOrderId();
        if (empty($_order)) {
            $_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        }

        return $_order;
    }

    public function getInstruction() {
        $paymentCode = $this->getOrderPaymentCode();
        $instruction = Mage::getStoreConfig('payment/' . $paymentCode . '/instructions');

        if (empty ($instruction)) {
            $instruction = Mage::getStoreConfig('payment/' . $paymentCode . '/message');
        }

        if (in_array($paymentCode, $this->getPaymentMethodKlikpay()) || in_array($paymentCode, $this->getPaymentMethodCc())) {
            $instruction = '';
        }

        return $instruction;
    }

    public function getOrderId() {
        if ($this->getRequest()->getParam('order_no')) {
            return $this->getRequest()->getParam('order_no');
        }
        else {
            return $this->_getData('order_id');
        }
    }

    public function getOrderPaymentCode() {
        return $this->getOrder()->getPayment()->getMethodInstance()->getCode();
    }

    public function getPaymentMethodBankTransfer() {
        return Mage::helper('paymethod')->getPaymentMethodBankTransfer();
    }

    public function getPaymentMethodKlikpay() {
        return Mage::helper('paymethod')->getPaymentMethodKlikpay();
    }

    public function getPaymentMethodCc() {
        return Mage::helper('paymethod')->getPaymentMethodCc();
    }

    public function getPaymentMethodVtdirect() {
        return Mage::helper('paymethod')->getPaymentMethodVtdirect();
    }

    public function getPaymentMethodVA() {
        return Mage::helper('paymethod')->getPaymentMethodVA();
    }

    public function getResponseCharge() {
        return Mage::registry('response_charge');
    }

    public function getThreedSecure() {
        return Mage::registry('threedsecure');
    }

    public function getDefaultResponseMessage($status, $message) {
        $result = '';

        if ($message) {
            $result = $message;
        }
        else {
            if ($status == 'success') {
                $result = Mage::getStoreConfig('payment/vtdirect/default_response_message_success');
            }
            else if ($status == 'failure') {
                $result = Mage::getStoreConfig('payment/vtdirect/default_response_message_failure');
            }
            else {
                $result = Mage::getStoreConfig('payment/vtdirect/charge_timeout_message');
            }
        }

        return $result;
    }

    public function getSubtotal() {
        $subtotal = '';
        if ($orderData = $this->getOrder()->getData()) {
            if (array_key_exists('subtotal', $orderData)) {
                $subtotal = $orderData['subtotal'];
            }
        }

        return $subtotal;
    }
}
