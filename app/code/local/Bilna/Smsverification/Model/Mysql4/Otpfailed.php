<?php
class Bilna_Smsverification_Model_Mysql4_Otpfailed extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('smsverification/otpfailed', 'otp_id');
    }
}
?>
