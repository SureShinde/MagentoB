<?php
class Bilna_Smsverification_Model_Otpfailed extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('smsverification/otpfailed');
    }

    /*
    public function toOptionArray()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $paymentList = array();
        foreach($payments as $idx => $val) {
            $paymentList[] = array('value' => $idx, 'label' => $idx);
        }
        return $paymentList;
    }
    */

}
