<?php
use Pheanstalk\Pheanstalk;
class Bilna_Smsverification_IndexController extends Mage_Core_Controller_Front_Action {
    public function IndexAction() {
        $incomingResponse = file_get_contents('php://input');
        Mage::log("Wavecell Incoming DR: ".$incomingResponse);
        $statusOpen = strpos($incomingResponse,'<Status>') + 8;
        $statusClosed = strpos($incomingResponse,'</Status>');
        $status = substr($incomingResponse,$statusOpen,($statusClosed-$statusOpen));
        if($incomingResponse != "") {
            if((strtoupper($status) == "DELIVERED TO CARRIER") || (strtoupper($status) == "DELIVERED TO DEVICE")) {
                $umidOpen = strpos($incomingResponse,'<UMID>') + 6;
                $umidClosed = strpos($incomingResponse,'</UMID>');
                $umid = substr($incomingResponse,$umidOpen,($umidClosed-$umidOpen));
                try {
                    $hostname = Mage::getStoreConfig('bilna_queue/beanstalkd_settings/hostname');
                    $pheanstalk = new Pheanstalk($hostname);
                    $pheanstalk->useTube('wavecell_dr')->put(json_encode(array('code' => $umid, 'status' => strtoupper($status),'timestamp' => strtotime("now"))));
                    Mage::log("Wavecell DR insert into que: ".json_encode(array('code' => $umid, 'status' => strtoupper($status))));
                }
                catch (Exception $e) {
                    Mage::logException($e);
                }

            }
        }
    }

}
