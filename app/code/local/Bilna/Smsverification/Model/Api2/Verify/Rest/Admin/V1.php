<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Smsverification_Model_Api2_Verify_Rest_Admin_V1 extends Bilna_Smsverification_Model_Api2_Verify_Rest
{
    protected function _create(array $data) {
        $customerId = $data['customer_id'];
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if(!$customer->getId()) {
            $this->_critical("Customer Does Not Exists");
        }

        try{
            $msisdn = Mage::Helper('smsverification')->validateMobileNumber($data['msisdn']);
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }

        //otp valid retry limit check
        $maxInvalidTime = Mage::getStoreConfig('bilna/smsverification/invalid_time_limit');
        $maxInvalidCount = Mage::getStoreConfig('bilna/smsverification/max_invalid');
        $OTPHistory = Mage::getModel('smsverification/otpfailed');

        $startDate = date('Y-m-d H:i:s',strtotime("-".Mage::getStoreConfig('bilna/smsverification/invalid_time_limit')." minutes",strtotime(Mage::getModel('core/date')->date('Y-m-d H:i:s'))));
        $failedData = $OTPHistory->getCollection()
            ->addFieldToFilter('customer_id',array('equal' => $data['customer_id']))
            ->addFieldToFilter('created_at',array('gteq' => $startDate));
        if(count($failedData) >= $maxInvalidCount) {
            $this->_critical('You Have reached max OTP Retry');
        }

        //Delete old otp failed data of customer
        $write = Mage::getSingleton("core/resource")->getConnection("core_write");
        $query = "DELETE FROM otp_failed WHERE customer_id=:customer_id AND created_at<:created_at";
        $binds = array(
            'customer_id' => $customerId,
            'created_at' => $startDate
        );
        $write->query($query, $binds);


        $OTPModel = Mage::getModel('smsverification/otplist');
        $OTPData = $OTPModel
            ->getCollection()
            ->addFilter('msisdn',array('equal' => $msisdn))
            ->addFilter('otp_code',array('equal' => $data['otp_code']))
            ->addFilter('type',array('equal' => 0))
            ->addFilter('customer_id',array('equal' => $data['customer_id']));
        if(count($OTPData) > 0) {
            $OTP = $OTPData->getFirstItem()->getData();
            $createdAt = strtotime($OTP['created_at']);
            $currentTime = strtotime(date('Y-m-d H:i:s'));
            $timeOut = Mage::getStoreConfig('bilna/smsverification/timeout');
            if (($currentTime - $createdAt) > ($timeOut * 60)) {
                $OTPData->getFirstItem()->delete();
                $this->_critical('Expired OTP Code');
            }

            $otherCustomer = Mage::getModel('customer/customer')->getCollection()
                            ->addAttributeToFilter('mobile_number',array('eq' => $data['msisdn']))
                            ->addAttributeToFilter('entity_id',array('neq' => $data['customer_id']))
                            ->getData();
            if(count($otherCustomer) > 0) {
                foreach($otherCustomer as $idx => $val) {
                    $customer = Mage::getModel('customer/customer')->load($val['entity_id']);
                    $customer->setVerifiedDate(NULL);
                    $customer->save();
                }
            }

            $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
            $customer->setMobileNumber($data['msisdn']);
            $customer->setVerifiedDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
            $customer->save();

            $data = $OTPData->getFirstItem();
            $data->setType(1);
            $data->save();
            $query = "DELETE FROM otp_failed WHERE customer_id=:customer_id";
            $binds = array(
                'customer_id' => $customerId,
            );
            $write->query($query, $binds);
            return $data;
        } else {
            $query = "INSERT INTO otp_failed SET customer_id=:customer_id,otp_code=:otp,created_at=NOW()";
            $binds = array(
                'otp'   => $data['otp_code'],
                'customer_id' => $customerId
            );
            $OTPModel = $write->query($query, $binds);
            $this->_critical('Invalid OTP Code');
        }

    }

}
