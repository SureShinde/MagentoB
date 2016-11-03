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
            ->addFilter('otp_code',array('equal' => $data['otp_code']))
            ->addFilter('customer_id',array('equal' => $data['customer_id']));
        if(count($OTPData) > 0) {
            $OTP = $OTPData->getFirstItem()->getData();
            $createdAt = strtotime($OTPDetail['created_at']);
            $currentTime = strtotime(date('Y-m-d H:i:s'));
            $timeOut = $body = Mage::getStoreConfig('bilna/smsverification/timeout');
            if (($currentTime - $createdAt) > ($timeOut * 60)) {
                $OTPData->getFirstItem()->delete();
                $this->_critical('Expired OTP Code');
            }

            $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
            $customer->setMobileNumber($data['msisdn']);
            $customer->setVerifiedDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
            $customer->save();
            return $OTPData->getFirstItem()->delete();
        } else {
            $this->_critical('Invalid OTP Code');
        }

    }

}
