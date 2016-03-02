<?php

/**
 * API2 class for customer (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Customer_Address_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Customer_Rest
{
	/**
     * Set customer for shopping cart
     *
     * @param array $data
     * @return int
     */
    protected function _create(array $data)
    {
//print_r($data);die;
    	$quoteId = $data['entity_id'];
        $storeId = isset($data['store_id']) ? $data['store_id'] : 1;
        $address =  $data['address'];

        try {
            $quote = $this->_getQuote($quoteId, $storeId);

            $customerAddressData = $this->_prepareCustomerAddressData($address);
            if (is_null($customerAddressData)) {
                throw Mage::throwException('Customer Address Data Empty');
            }

            foreach ($customerAddressData as $addressItem)
            {
                $address = Mage::getModel("sales/quote_address");

                $addressMode = $addressItem['mode'];
                unset($addressItem['mode']);

                if (!empty($addressItem['entity_id'])) {
                    $customerAddress = $this->_getCustomerAddress($addressItem['entity_id']);
                    if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
                        throw Mage::throwException('address not belong customer');
                    }
                    $address->importCustomerAddress($customerAddress);

                } else {
                    $address->setData($addressItem);
                }

                $address->implodeStreetAddress();
                $validateRes = $address->validate();

                /*if ( $validateRes !==true ) {
                    throw Mage::throwException('customer address invalid');
                }*/

                switch($addressMode)
                {
                    case self::ADDRESS_BILLING:
                        $address->setEmail($quote->getCustomerEmail());

                        if (!$quote->isVirtual()) {
                            $usingCase = isset($addressItem['use_for_shipping']) ? (int)$addressItem['use_for_shipping'] : 0;
                            switch($usingCase) {
                            case 0:
                                $shippingAddress = $quote->getShippingAddress();
                                $shippingAddress->setSameAsBilling(0);
                                break;
                            case 1:
                                $billingAddress = clone $address;
                                $billingAddress->unsAddressId()->unsAddressType();

                                $shippingAddress = $quote->getShippingAddress();
                                $shippingMethod = $shippingAddress->getShippingMethod();
                                $shippingAddress->addData($billingAddress->getData())
                                    ->setSameAsBilling(1)
                                    ->setShippingMethod($shippingMethod)
                                    ->setCollectShippingRates(true);
                                break;
                            }
                        }
                        $quote->setBillingAddress($address);
                        break;
                    case self::ADDRESS_SHIPPING:
                        $address->setCollectShippingRates(true)
                                ->setSameAsBilling(0);
                        $quote->setShippingAddress($address);
                        break;
                }

            }

            $quote
                ->collectTotals()
                ->save();

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $this->_getLocation($quote);
    }

    /**
     * Prepare customer entered data for implementing
     *
     * @param  array $data
     * @return array
     */
    protected function _prepareCustomerAddressData($data)
    {
        if (!is_array($data) || !is_array($data[0])) {
            return null;
        }
        
        $dataAddresses = array();
        foreach($data as $addressItem) {
            if (isset ($this->_attributesMap['quote_address'])) {
                foreach ($this->_attributesMap['quote_address'] as $attributeAlias=>$attributeCode) {
                     if(isset($addressItem[$attributeAlias]))
                     {
                         $addressItem[$attributeCode] = $addressItem[$attributeAlias];
                         unset($addressItem[$attributeAlias]);
                     }
                }
            }
            
            $dataAddresses[] = $addressItem;
        }
        return $dataAddresses;
    }

    protected function _retrieve()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $quote = $this->__getCollection($quoteId);

        $quoteDataRaw = $quote->getData();
        
        if(empty($quoteDataRaw)){
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        $quoteData = $quoteDataRaw[0];
        $addresses = $this->_getAddresses(array($quoteData['entity_id']));
        $items     = $this->_getItems(array($quoteData['entity_id']));

        if ($addresses) {
            $quoteData['addresses'] = $addresses[$quoteData['entity_id']];
        }
        if ($items) {
            $quoteData['quote_items'] = $items[$quoteData['entity_id']];
        }
        
        return $quoteData;
    }
}