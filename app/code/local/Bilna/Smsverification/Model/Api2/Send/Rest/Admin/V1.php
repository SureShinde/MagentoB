<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Smsverification_Model_Api2_Send_Rest_Admin_V1 extends Bilna_Smsverification_Model_Api2_Send_Rest
{
    protected function _create(array $data) {
        $customerId = $data['customer_id'];
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if(!$customer->getId()) {
            $this->_critical("Customer Does Not Exists");
        }

        $OTPModel = Mage::getModel('smsverification/otplist');

        $maxOTP = Mage::getStoreConfig('bilna/smsverification/max_otp');
        if ((int) $maxOTP > 0) {
            $timeChecking = Mage::getStoreConfig('bilna/smsverification/time_limit');
            $startFrom = date('Y-m-d H:i:s', strtotime(Mage::getModel('core/date')->date('Y-m-d H:i:s')." -".$timeChecking." minutes"));

            $OTPData = $OTPModel
                ->getCollection()
                ->setOrder('created_at','DESC')
                ->addFilter('customer_id',array('equal' => $customerId))
                ->addFilter('type',array('equal' => 0))
                ->addFieldToFilter('created_at',array('gteq' => $startFrom));

            if($maxOTP <= count($OTPData)) {
                $this->_critical("You have reach max OTP Request. Please Try Again later");
            }
        }

        $minChangeMobileNumber = Mage::getStoreConfig('bilna/smsverification/mindays');

        try{
            $msisdn = Mage::Helper('smsverification')->validateMobileNumber($data['msisdn']);
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
        $OTPData = $OTPModel
            ->getCollection()
            ->setOrder('created_at','DESC')
            ->addFilter('msisdn',array('equal' => $msisdn))
            ->addFilter('type',array('equal' => 1));

        if(count($OTPData) > 0) {
            $OTPDetail = $OTPData->getFirstItem()->getData();
            $lastUsed = strtotime($OTPDetail['created_at']);
            $current = strtotime(date('Y-m-d H:i:s'));
            $timeDiff = ($current - $lastUsed) / (60*60*24);
            if($timeDiff < $minChangeMobileNumber) {
                $this->_critical('This Number is already used before');
            }
        }

        $otp = $this->OTPGenerator();
        $body = Mage::getStoreConfig('bilna/smsverification/template');
        $body = str_replace("[OTP]", $otp, $body);
        try{
            Mage::Helper('smsverification')->sendSMS($msisdn,$body);
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }

        $write = Mage::getSingleton("core/resource")->getConnection("core_write");
        $query = "INSERT INTO otp_list SET msisdn=:msisdn,otp_code=:otp,created_at=:created_at,customer_id=:customer_id";
        $binds = array(
            'msisdn'    => $msisdn,//$data['msisdn'],
            'otp'   => $otp,
            'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s'),
            'customer_id' => $customerId
        );
        $OTPModel = $write->query($query, $binds);
        return $OTPModel;
    }

    protected function OTPGenerator() {
        $characters = '0123456789';
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
