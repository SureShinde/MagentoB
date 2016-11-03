<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Smsverification_Model_Api2_Verify_Rest_Admin_V1 extends Bilna_Smsverification_Model_Api2_Verify_Rest
{
    protected function _create(array $data) {
        $data['msisdn'] = substr($data['msisdn'],0,1) == "0" ? "62".substr($data['msisdn'],1) : $data['msisdn'];
        $OTPModel = Mage::getModel('smsverification/otplist');
        $OTPData = $OTPModel
            ->getCollection()
            ->addFilter('msisdn',array('equal' => $data['msisdn']))
            ->addFilter('otp_code',array('equal' => $data['otp_code']));
        if(count($OTPData) > 0) {
            return $OTPData->getFirstItem()->delete();
        } else {
            $this->_critical('Invalid OTP Code');
        }

    }

}
