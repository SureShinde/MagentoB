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
        $otp = $this->OTPGenerator();

        $url = Mage::getStoreConfig('bilna/smsverification/url_api');
        $accountID = Mage::getStoreConfig('bilna/smsverification/account_id');
        $subAccountID = Mage::getStoreConfig('bilna/smsverification/sub_account_id');
        $password = Mage::getStoreConfig('bilna/smsverification/password');
        $source = Mage::getStoreConfig('bilna/smsverification/source');
        $umID = Mage::getStoreConfig('bilna/smsverification/umid');

        $ScheduledDateTime = str_replace(" ","T",Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        $ScheduledDateTime = str_replace(":", "%3A", $ScheduledDateTime);
        $url = $url."?AccountId=".$accountID."&SubAccountId=".$subAccountID."&Password=".$password."&Destination=".$msisdn."&Source=".$source."&Body=".urlencode($otp)."&Encoding=ASCII&ScheduledDateTime=".$ScheduledDateTime."&UMID=".$umID;
        //print $url;exit;
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

        //exit;
        /*
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array( 'timeout' => 15));
        $curl->write(Zend_Http_Client::GET, $feed_url, '1.0');
        $data = $curl->read();
        */
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
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
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
