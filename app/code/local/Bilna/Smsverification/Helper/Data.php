<?php
class Bilna_Smsverification_Helper_Data extends Mage_Core_Helper_Abstract {
    public function validateMobileNumber($mobileNumber) {
        if (trim($mobileNumber) != "") {
            $mobileNumber = preg_replace('/[^\d]/g', '', $mobileNumber); // remove non-digits
            $mobileNumber = preg_replace('/^(62|0)/', '', $mobileNumber); // remove preceding 62 or 0
            $mobileNumber = '62' . $mobileNumber; // add 62

            if (!preg_match ('/^[0-9]{'.Mage::getStoreConfig('bilna/smsverification/min_msisdn').','.Mage::getStoreConfig('bilna/smsverification/max_msisdn').'}$/', $mobileNumber)) {
                throw Mage::throwException("Nomor handphone yang dimasukkan tidak valid");
            }
        }
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
            Mage::log("Send SMS error: ".$err,Zend_Log::ERR);
            Mage::throwException("SMS gagal dikirimkan karena kendala teknis. Hubungi CS Orami untuk bantuan");
        }
        $responseArray = simplexml_load_string($response);
        $code = str_replace("RECEIVED:","",$responseArray[0]);
        return $code;
    }

    public function validateCouponUsage($quote) {
        $isEnabledVerification = Mage::getStoreConfig('bilna/smsverification/voucher_check');
        $isActiveModule = Mage::getStoreConfig('bilna/smsverification/verification_active');
        if (($isEnabledVerification) && ($isActiveModule)) {
            $method = $quote->getCheckoutMethod(true);
            if ($method == 'customer') {
                $customerData = Mage::getModel('customer/customer')->load($quote->getCustomerId());
                if(($customerData->getMobileNumber() == "") || ($customerData->getVerifiedDate()) == "") {
                    $newCustomerDuration = (int) Mage::getStoreConfig('bilna/smsverification/duration');
                    $interval  = abs((strtotime(date('Y-m-d H:i:s'))) - strtotime($customerData->getCreatedAt())) / 60;

                    if ($interval > $newCustomerDuration) {
                        Mage::throwException("Lakukan verifikasi nomor handphone sebelum melanjutkan");
                    }
                }
            }
        }
    }

    public function isEnabledValidate() {
        $isEnabledValidate = Mage::getStoreConfig('bilna/smsverification/validate_cod');
        $isActiveModule = Mage::getStoreConfig('bilna/smsverification/verification_active');
        return  ($isEnabledValidate && $isActiveModule);
    }

}
