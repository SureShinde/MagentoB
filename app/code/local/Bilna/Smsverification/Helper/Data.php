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
        $urlAPI = Mage::getStoreConfig('bilna/smsverification/url_api');
        $accountID = Mage::getStoreConfig('bilna/smsverification/account_id');
        $subAccountID = Mage::getStoreConfig('bilna/smsverification/sub_account_id');
        $password = Mage::getStoreConfig('bilna/smsverification/password');
        $source = Mage::getStoreConfig('bilna/smsverification/source');
        $umID = Mage::getStoreConfig('bilna/smsverification/umid');
        $ScheduledDateTime="";
        $url = $urlAPI."?AccountId=".$accountID."&SubAccountId=".$subAccountID."&Password=".$password."&Destination=".$msisdn."&Source=".$source."&Body=".urlencode($body)."&Encoding=ASCII&ScheduledDateTime=".$ScheduledDateTime."&UMID=".$umID;

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
            Mage::log("Send SMS error: ".json_encode($err));
            Mage::throwException("Failed to Send SMS");
        }
        $drData = simplexml_load_string($response);
        $drID = str_replace("RECEIVED:","",$drData[0]);
        return $drID;
    }

}
