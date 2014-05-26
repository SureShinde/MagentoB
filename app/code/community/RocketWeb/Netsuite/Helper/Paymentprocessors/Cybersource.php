<?php
class RocketWeb_Netsuite_Helper_Paymentprocessors_Cybersource extends RocketWeb_Netsuite_Helper_Paymentprocessors_Abstract {
    public function addProcessorSpecificInfromationToNetSuiteOrder(SalesOrder $netsuiteOrder,Mage_Sales_Model_Order $magentoOrder) {
        $paymentObject = $magentoOrder->getPayment();
        $expireDate = new DateTime();
        $expireDate->setDate($paymentObject->getCcExpYear(),$paymentObject->getCcExpMonth(),1);

        $netsuiteOrder->ccExpireDate = $expireDate->format(DateTime::ISO8601);
        $netsuiteOrder->pnRefNum = $paymentObject->getCcTransId();
        $netsuiteOrder->getAuth = false;
        $netsuiteOrder->ccApproved = true;

        return $netsuiteOrder;
    }

    //This is changed so that we can get the oldest comment, not the newest
    public function getCybersourceTransactionComment(Mage_Sales_Model_Order $order) {
        $signature = Mage::helper('cybersource')->prepareSignature();
        $comment = null;

        $orderHistory = $order->getAllStatusHistory();
        foreach($orderHistory as $orderHistoryItem) {
            if(strpos($orderHistoryItem->getComment(),$signature)!==FALSE) {
                $comment = $orderHistoryItem->getComment();
            }
        }

        return $comment;

    }

    public function getCybersourceTransactionDataFromComment($commentString) {
        $ret = array();

        $lines = explode(';',$commentString);
        foreach($lines as $line) {
            $data = explode(':',$line);
            if(count($data) == 2) {
                $key = trim($data[0]);
                $value = trim($data[1]);

                switch($key) {
                    case 'CyberSource Payment Decision':
                        $ret['decision'] = $value;
                        break;
                    case 'Authorization Reason Code':
                        $ret['auth_reason_code'] = $value;
                        break;
                    case 'Authorization Code':
                        $ret['auth_code'] = $value;
                        break;
                    case 'Reason Code':
                        $ret['reason_code'] = $value;
                        break;
                    case 'AVS Code':
                        $ret['avs_code'] = $value;
                        break;
                    case 'CVN Code':
                        $ret['cvn_code'] = $value;
                        break;
                }
            }
        }

        return $ret;
    }
}