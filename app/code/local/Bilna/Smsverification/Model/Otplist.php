<?php
class Bilna_Smsverification_Model_Otplist extends Mage_Core_Model_Abstract
{
    const TYPE_REFUND = 0;
    const TYPE_SPENDING = 1;
    const TYPE_WITHDRAWAL = 2;
    const STATUS_PENDING = 0;
    const STATUS_FINAL = 1;
    const STATUS_CANCELLED = 2;
    protected function _construct()
    {
        $this->_init('smsverification/otplist');
    }

}
