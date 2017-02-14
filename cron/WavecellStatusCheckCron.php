<?php
require_once realpath(dirname(__FILE__)).'/../app/Mage.php';

class SMSStatusCheck {
    const LIMIT = 100;
    private $smsDrModel;

    public function __construct()
    {
        Mage::app();
        $this->smsDrModel = Mage::getModel('smsverification/smsdr');
    }

    private function getData($id) {
        try{
            $smsDrData = $this->smsDrModel->getCollection()->addFieldToFilter('sms_id',array('gt' => $id));
            $smsDrData->setPageSize(self::LIMIT);
            $smsDrData->getData();
            return $smsDrData;
        } catch (Exception $e) {
            Mage::log("Send SMS error: ".$e,Zend_Log::ERR);
            return array();
        }
    }

    public function execute() {
        $urlApi = Mage::getStoreConfig('bilna/smsverification/sms_dlr');
        $accountId = Mage::getStoreConfig('bilna/smsverification/account_id');
        $subAccountId = Mage::getStoreConfig('bilna/smsverification/sub_account_id');
        $password = Mage::getStoreConfig('bilna/smsverification/password');
        $setCancelled = (Mage::getStoreConfig('bilna/smsverification/canceled_cod') != '') ? Mage::getStoreConfig('bilna/smsverification/canceled_cod') : 1;


        $id = 0;
        while(true) {
            $data = $this->getData($id);
            if (count($data) < 1) break;
            foreach($data as $idx => $val) {
                $id = $val['sms_id'];
                $url = $urlApi."?AccountId=".$accountId."&SubAccountId=".$subAccountId."&Password=".$password."&UMID=".$val['code'];
                $fileContent = file_get_contents($url);
                if ($fileContent != "") {
                    $statusOpen = strpos($fileContent,"<Status>")+8;
                    $statusClosed = strpos($fileContent, "</Status>");
                    $status = substr($fileContent,$statusOpen,$statusClosed-$statusOpen);
                    $order = Mage::getModel('sales/order')->load($val['order_id']);
                    if (Mage::Helper('cod')->isCodOrder($order)) {
                        if ((strtoupper($status) == "DELIVERED TO CARRIER") || (strtoupper($status) == "DELIVERED TO DEVICE")) {
                            $order->setStatus('processing_cod',true)->save();
                            //insert into table message
                            $priority = 2;
                            $message = Mage::getModel('rocketweb_netsuite/queue_message');
                            $paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode();
                            $msg = "\n".date('YmdHis')." increment id ".$order->getIncrementId(). " | entity_id ".$order->getId() ." | paymentMethodCode ".$paymentMethodCode;
                            error_log($msg, 3, Mage::getBaseDir('var') . DS . 'log'.DS.'netsuite_order.log');
                            $message->create(RocketWeb_Netsuite_Model_Queue_Message::ORDER_PLACE, $order->getId(), RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
                            Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE)->send($message->pack(), $priority);
                        }
                        else {
                            $order->addStatusToHistory($order->getStatus(), "Validate status: ".$status, false);
                            if($setCancelled) {
                                $order->cancel();
                            }
                            $order->save();
                        }
                    }
                    $this->smsDrModel->load($val['sms_id'])->delete();
                }
            }
        }
    }

}

$cronObj = new SMSStatusCheck();
$cronObj->execute();
?>
