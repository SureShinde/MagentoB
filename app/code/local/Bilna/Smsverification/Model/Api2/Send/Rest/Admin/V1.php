<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Smsverification_Model_Api2_Send_Rest_Admin_V1 extends Bilna_Smsverification_Model_Api2_Send_Rest
{
    protected function _create(array $data) {
        $customerId = $this->getRequest()->getParam('customer_id') ? $this->getRequest()->getParam('customer_id') : '';
        $msisdn = $data['msisdn'];
        $msisdn = substr($msisdn,0,1) == "0" ? "62".substr($msisdn,1) : $msisdn;
        $otp = $this->OTPGenerator();

        $url = Mage::getStoreConfig('bilna/smsverification/url_api');
        $accountID = Mage::getStoreConfig('bilna/smsverification/account_id');
        $subAccountID = Mage::getStoreConfig('bilna/smsverification/sub_account_id');
        $password = Mage::getStoreConfig('bilna/smsverification/password');
        $source = Mage::getStoreConfig('bilna/smsverification/source');
        $umID = Mage::getStoreConfig('bilna/smsverification/umid');
        $body = Mage::getStoreConfig('bilna/smsverification/template');

        //$ScheduledDateTime = str_replace(" ","T",Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        //$ScheduledDateTime = str_replace(":", "%3A", $ScheduledDateTime);
        $ScheduledDateTime="";
        $body = str_replace("[OTP]", $otp, $body);
        $url = $url."?AccountId=".$accountID."&SubAccountId=".$subAccountID."&Password=".$password."&Destination=".$msisdn."&Source=".$source."&Body=".urlencode($body)."&Encoding=ASCII&ScheduledDateTime=".$ScheduledDateTime."&UMID=".$umID;

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
            $this->_critical('Failed to Send OTP');
        }

        $OTPModel = Mage::getModel('smsverification/otplist');
        $OTPModel->setData('msisdn',$msisdn);
        $OTPModel->setData('otp_code',$otp);
        $OTPModel->setData('created_at',Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        $OTPModel->save();

        return $OTPModel;
    }

    protected function OTPGenerator() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $length = 6;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    protected function _retrieve() {

    }

}
