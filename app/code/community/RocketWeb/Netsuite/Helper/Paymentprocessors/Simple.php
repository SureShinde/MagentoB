<?php
class RocketWeb_Netsuite_Helper_Paymentprocessors_Simple extends RocketWeb_Netsuite_Helper_Paymentprocessors_Abstract {
    public function addProcessorSpecificInfromationToNetSuiteOrder(SalesOrder $netsuiteOrder,Mage_Sales_Model_Order $magentoOrder) {
        return $netsuiteOrder;
    }
}