<?php
class Bilna_Wholesale_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'pickup';

    public function getAllowedMethods()
    {
        return array(
            'pickup' => 'Pickup'
        );
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');
        $result->append($this->_getPickupData());

        return $result;
    }

    protected function _getPickupData()
    {
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('large');
        $rate->setMethodTitle($this->getConfigData('name'));
        $rate->setMethodDescription('test');
        $rate->setPrice($this->getConfigData('price'));
        $rate->setCost($this->getConfigData('handling_fee'));

        return $rate;
    }
}
