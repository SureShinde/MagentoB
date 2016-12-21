<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Smsverification_Model_Api2_Verify_Rest_Admin_V1 extends Bilna_Smsverification_Model_Api2_Verify_Rest
{
    protected function _create(array $data) {
        try{
            $msisdn = Mage::Helper('smsverification')->validateMobileNumber($data['msisdn']);
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }

        $customerId = $data['customer_id'];
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if(!$customer->getId()) {
            $this->_critical("Customer tidak terdaftar");
        }

        //otp valid retry limit check
        $maxInvalidCount = (int)Mage::getStoreConfig('bilna/smsverification/max_invalid');
        if($maxInvalidCount > 0 ) {
            $maxInvalidTime = (int)Mage::getStoreConfig('bilna/smsverification/invalid_time_limit');
            $OTPHistory = Mage::getModel('smsverification/otpfailed');

            $startDate = date('Y-m-d H:i:s',strtotime("-".$maxInvalidTime." minutes",strtotime(Mage::getModel('core/date')->date('Y-m-d H:i:s'))));
            $failedData = $OTPHistory->getCollection()
                ->addFieldToFilter('customer_id',array('equal' => $data['customer_id']))
                ->addFieldToFilter('created_at',array('gteq' => $startDate));
            if(count($failedData) >= $maxInvalidCount) {
                $this->_critical('Anda telah memasukkan kode OTP yang salah berulang kali. Coba kembali dalam beberapa saat');
            }
        }

        $write = Mage::getSingleton("core/resource")->getConnection("core_write");

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
            $currentTime = strtotime(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
            $timeOut = Mage::getStoreConfig('bilna/smsverification/timeout');
            if ((int) $timeOut > 0) {
                if (($currentTime - $createdAt) > ($timeOut * 60)) {
                    $OTPData->getFirstItem()->delete();
                    $this->_critical('Kode OTP Kadaluarsa. Silakan minta kode OTP baru');
                }
            }
            $otherCustomer = Mage::getModel('customer/customer')->getCollection()
                            ->addAttributeToFilter('mobile_number',array('eq' => $msisdn))
                            ->addAttributeToFilter('entity_id',array('neq' => $data['customer_id']))
                            ->getData();
            if(count($otherCustomer) > 0) {
                foreach($otherCustomer as $idx => $val) {
                    $otherCust = Mage::getModel('customer/customer')->load($val['entity_id']);
                    $otherCust->setVerifiedDate(NULL);
                    $otherCust->save();
                }
            }

            $customer->setMobileNumber($msisdn);
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
            //Delete old otp failed data of customer and insert new otp failed data
            $query = "DELETE FROM otp_failed WHERE customer_id=:customer_id AND created_at<:start_date; INSERT INTO otp_failed SET customer_id=:customer_id,otp_code=:otp,created_at=:created_at";
            $binds = array(
                'customer_id' => $customerId,
                'start_date' => $startDate,
                'otp'   => $data['otp_code'],
                'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
            );
            $write->query($query, $binds);
            $this->_critical('Kode OTP Salah. Silakan coba kembali');
        }

    }

}
