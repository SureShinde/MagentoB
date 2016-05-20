<?php
class Bilna_Expressshipping_Model_Expressshipping extends Mage_Core_Model_Abstract
{
    /**
     * Function to get all active payment method data
     * @return array
     */
    public function toOptionArray()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $methods = array(array('value' => '', 'label' => Mage::helper('adminhtml')->__('Please Select')));

        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
            );
        }

        return $methods;
    }
}
