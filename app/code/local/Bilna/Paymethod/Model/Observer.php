<?php
/**
 * Description of Bilna_Paymethod_Model_Observer
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer extends Varien_Event_Observer {
    protected $_codeVirtualAccountBCA = 'virtualaccountbca';

    public function salesOrderLog($observer) {
        if (Mage::helper('paymethod')->salesOrderLogActive()) {
            $order = $observer->getEvent()->getOrder();
            $dataArr = array (
                'orderId' => $order->getId(),
                'incrementId' => $order->getIncrementId(),
                'customerEmail' => $order->getCustomerEmail(),
                'paymentMethod' => $order->getPayment()->getMethodInstance()->getCode(),
                'status' => $order->getStatus(),
                'subtotal' => $order->getSubtotal(),
                'grandTotal' => $order->getGrandTotal(),
                'createdAt' => Mage::helper('paymethod')->getMagentoDateFormat($order->getCreatedAt()),
                'updatedAt' => Mage::helper('paymethod')->getMagentoDateFormat($order->getUpdatedAt()),
            );
            $message = sprintf("%s => %s" , $order->getIncrementId(), json_encode($dataArr));
            Mage::helper('paymethod')->salesOrderLog($message);
        }
    }

    /**
     * Function to disable send new order email for the specific payment method
     * @param $observer
     */
    public function disableNewOrderEmail (Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        // Disable the Send New Order for VA BCA payment method
        if ($order->getPayment()->getMethodInstance()->getCode() == $this->_codeVirtualAccountBCA){
            $order->setCanSendNewEmailFlag(false);
        }
        return;
    }

    /**
     * Function to trigger send email after success page loaded in front end
     * @param $observer
     */
    public function sendNewOrderEmail(Varien_Event_Observer $observer)
    {
        //$orderId = $observer->getEvent()->getOrderIds();
        //$order = Mage::getModel('sales/order')->load($orderId);
        $order = $observer->getEvent()->getOrder();

        // Send the New Order Email for the VA BCA payment method
        if ($order->getPayment()->getMethodInstance()->getCode() == $this->_codeVirtualAccountBCA) {
            $order->setCanSendNewEmailFlag(true);
            $order->sendNewOrderEmail();
        }
        return;
    }
}
