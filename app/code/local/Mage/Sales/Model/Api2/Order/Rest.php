<?php
/**
 * Description of Mage_Sales_Model_Api2_Order_Rest
 * 
 * @path   app/code/local/Mage/Sales/Model/Api2/Order/Rest.php
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Mage_Sales_Model_Api2_Order_Rest extends Mage_Sales_Model_Api2_Order {
    protected function _getPaymentTitle($order) {
        try {
            if ($paymentTitle = $order->getPayment()->getMethodInstance()->getTitle()) {
                return $paymentTitle;
            }
            
            return '';
        }
        catch (Exception $ex) {
            return '';
        }
    }

    protected function _getAdditionalInfo($order) {
        $result = array ();
        
        //- get BIN Number for Credit Card
        if ($this->_isCreditCard($order->getPaymentMethod())) {
            $result['bin_number'] = $order->getPayment()->getCcBins();
        }
        
        //- get virtual account number
        if ($vaNumber = $order->getPayment()->getVaNumber()) {
            $result['va_number'] = $vaNumber;
        }
        
        return $result;
    }
    
    protected function _isCreditCard($paymentCode) {
        $creditCards = Mage::helper('paymethod')->getPaymentMethodCc();
        
        if (in_array($paymentCode, $creditCards)) {
            return true;
        }
        
        return false;
    }

    protected function _getOrderShipment($order) {
        $orderShipment = $order->getShipmentsCollection();
        $result = [];

        if ($orderShipment) {
            foreach ($orderShipment as $shipment) {
                $shipmentId = $shipment->getId();
                $result[$shipmentId] = $shipment->getData();
                
                if ($shipmentTrack = $shipment->getAllTracks()) {
                    foreach ($shipmentTrack as $track) {
                        $result[$shipmentId]['tracking_info'][] = [
                            'title' => $track->getTitle(),
                            'number' => $track->getNumber(),
                        ];
                    }
                }
            }
            
            return $result;
        }

        return false;
    }
}
