<?php
require_once realpath(dirname(__FILE__)).'/../app/Mage.php';

class SMSStatusCheck {
    const LIMIT = 2;
    private $smsDrModel;
    public function __construct()
    {
        Mage::app();
        $this->smsDrModel = Mage::getModel('smsverification/smsdr');
    }

    private function getData($id) {
        try{
            $smsDrData = $this->smsDrModel->getCollection()->addFieldToFilter('sms_id',array('gt' => $id));
            $smsDrData->setPageSize(SELF::LIMIT);
            $smsDrData->getData();
        } catch (Exception $e) {
            var_dump($e);
        }
        return $smsDrData;
    }

    public function execute() {
        $urlApi = Mage::getStoreConfig('bilna/smsverification/sms_dlr');
        $accountId = Mage::getStoreConfig('bilna/smsverification/account_id');
        $subAccountId = Mage::getStoreConfig('bilna/smsverification/sub_account_id');
        $password = Mage::getStoreConfig('bilna/smsverification/password');
        $page = 1;
        $id = 0;
        $stop = 0;
        while(true) {
            if($stop > 0) break;

            $data = $this->getData($id);
            if(count($data) < 1) break;
            foreach($data as $idx => $val) {
                $id = $val['sms_id'];
                $url = $urlApi."?AccountId=".$accountId."&SubAccountId=".$subAccountId."&Password=".$password."&UMID=".$val['code'];
                $fileContent = file_get_contents($url);
                $tagOpen = strpos($fileContent,'<Status>') + 8;
                $tagClosed = strpos($fileContent,'</Status>');
                $status = substr($fileContent,$tagOpen,($tagClosed-$tagOpen));
                if($fileContent != "") {
                    if((strtoupper($status) == "DELIVERED TO CARRIER") || (strtoupper($status) == "DELIVERED TO DEVICE")) {
                        Mage::getModel('sales/order')->load($val['order_id'])->setStatus(Mage::getStoreConfig('payment/cod/order_status'),true)->save();
                    }
                    $this->smsDrModel->load($val['sms_id'])->delete();
                }
            }
            $page++;
        }
    }

}

$cronObj = new SMSStatusCheck();
$cronObj->execute();
?>
