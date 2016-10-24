<?php
class Bilna_Wholesale_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
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
        if (
            !Mage::getSingleton('checkout/session')
                ->getQuote()
                ->getIsWholesale()
        ) {
            return;
        }

        $result = Mage::getModel('shipping/rate_result');
        $result->append($this->_getPickupData());

        return $result;
    }

    protected function _getPickupData()
    {
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethodTitle($this->getConfigData('name'));
        $rate->setPrice($this->getConfigData('price'));

        return $rate;
    }
}

