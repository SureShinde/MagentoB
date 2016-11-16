<?php
class Bilna_Smsverification_Helper_Data extends Mage_Core_Helper_Abstract {
    public function validateMobileNumber($mobileNumber) {
        if(trim($mobileNumber) != ""){
            $mobileNumber = str_replace(array("+","-",".","(",")"," "), "", $mobileNumber);
            if (!preg_match ('/^[0-9]{'.Mage::getStoreConfig('bilna/smsverification/min_msisdn').','.Mage::getStoreConfig('bilna/smsverification/max_msisdn').'}$/', $mobileNumber)) {
                throw Mage::throwException("Invalid Mobile Number");
            }
        }
        $mobileNumber = substr($mobileNumber,0,1) == "0" ? "62".substr($mobileNumber,1) : $mobileNumber;
        return $mobileNumber;
    }

    public function sendSMS($msisdn,$body) {
        $urlApi = Mage::getStoreConfig('bilna/smsverification/url_api');
        $accountId = Mage::getStoreConfig('bilna/smsverification/account_id');
        $subAccountId = Mage::getStoreConfig('bilna/smsverification/sub_account_id');
        $password = Mage::getStoreConfig('bilna/smsverification/password');
        $source = Mage::getStoreConfig('bilna/smsverification/source');
        $umId = Mage::getStoreConfig('bilna/smsverification/umid');
        $ScheduledDateTime="";
        $url = $urlApi."?AccountId=".$accountId."&SubAccountId=".$subAccountId."&Password=".$password."&Destination=".$msisdn."&Source=".$source."&Body=".urlencode($body)."&Encoding=ASCII&ScheduledDateTime=".$ScheduledDateTime."&UMID=".$umId;

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Mage::log("Send SMS error: ".$err);
            Mage::throwException("Failed to Send SMS");
        }
        $responseArray = simplexml_load_string($response);
        $code = str_replace("RECEIVED:","",$responseArray[0]);
        return $code;
    }

}
