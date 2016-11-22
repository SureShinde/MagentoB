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

        $id = 0;
        while(true) {
            $data = $this->getData($id);
            if(count($data) < 1) break;
            foreach($data as $idx => $val) {
                $id = $val['sms_id'];
                $url = $urlApi."?AccountId=".$accountId."&SubAccountId=".$subAccountId."&Password=".$password."&UMID=".$val['code'];
                $fileContent = simplexml_load_string(file_get_contents($url));
                $status = isset($fileContent->Status) ? $fileContent->Status : '';
                if($status != "") {
                    if((strtoupper($status) == "DELIVERED TO CARRIER") || (strtoupper($status) == "DELIVERED TO DEVICE")) {
                        Mage::getModel('sales/order')->load($val['order_id'])->setStatus(Mage::getStoreConfig('payment/cod/order_status'),true)->save();
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
