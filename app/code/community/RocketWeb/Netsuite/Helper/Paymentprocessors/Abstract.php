<?php
abstract class RocketWeb_Netsuite_Helper_Paymentprocessors_Abstract extends Mage_Core_Helper_Abstract {
    abstract public function addProcessorSpecificInfromationToNetSuiteOrder(SalesOrder $netsuiteOrder,Mage_Sales_Model_Order $magentoOrder);
}