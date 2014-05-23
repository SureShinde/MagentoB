<?php
class RocketWeb_Netsuite_Helper_Paymentprocessors_Paypalexpress extends RocketWeb_Netsuite_Helper_Paymentprocessors_Abstract {
    public function addProcessorSpecificInfromationToNetSuiteOrder(SalesOrder $netsuiteOrder,Mage_Sales_Model_Order $magentoOrder) {
        $paymentObject = $magentoOrder->getPayment();

        $netsuiteOrder->payPalTranId = $paymentObject->getLastTransId();
        //$netsuiteOrder->payPalStatus = $paymentObject->getAdditionalInformation('paypal_payment_status');

        return $netsuiteOrder;
    }
}