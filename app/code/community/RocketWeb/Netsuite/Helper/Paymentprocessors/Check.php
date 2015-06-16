<?php
class RocketWeb_Netsuite_Helper_Paymentprocessors_Check extends RocketWeb_Netsuite_Helper_Paymentprocessors_Abstract {
    public function addProcessorSpecificInfromationToNetSuiteOrder(SalesOrder $netsuiteOrder,Mage_Sales_Model_Order $magentoOrder) {
        $poNum = $magentoOrder->getPayment()->getPoNumber();
        if(!empty($poNum)) {
            $netsuiteOrder->otherRefNum =  $magentoOrder->getPayment()->getPoNumber();
        }
        return $netsuiteOrder;
    }
}