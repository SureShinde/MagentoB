<?php
class RocketWeb_Netsuite_Helper_Mapper_Address extends RocketWeb_Netsuite_Helper_Mapper {
    public function getBillingAddressNetsuiteFormatFromOrderAddress(Mage_Sales_Model_Order_Address $address) {
        $netsuiteAddress = new BillAddress();

        $lengthStreet1 = strlen(trim($address->getStreet1()));
        $lengthStreet2 = strlen(trim($address->getStreet2()));
        $street1 = substr(trim($address->getStreet1()), 0, 150);
        $sisa = "";
        if( $lengthStreet1 > 150 ) $sisa = substr(trim($address->getStreet1()), 150, $lengthStreet1);
        $street2 = substr(trim($sisa." ".$address->getStreet2()), 0, 150);

        $netsuiteAddress->billAddr1 = $street1;//$address->getStreet1();
        $netsuiteAddress->billAddr2 = $street2;//$address->getStreet2();
        $netsuiteAddress->billCity = $address->getCity();
        $country = Mage::getModel('directory/country')->loadByCode($address->getCountry());
        $netsuiteAddress->billCountry = Mage::helper('rocketweb_netsuite/transform')->transformCountryCode($country->getCountryId());
        $netsuiteAddress->billAddressee = $address->getName();
        $netsuiteAddress->billPhone = $address->getTelephone();
        $netsuiteAddress->billState =  $address->getRegionCode();
        $netsuiteAddress->billZip = $address->getPostcode();

        Mage::dispatchEvent('netsuite_bill_address_create_before',array('netsuite_address'=>$netsuiteAddress));

        return $netsuiteAddress;
    }

    public function getShippingAddressNetsuiteFormatFromOrderAddress(Mage_Sales_Model_Order_Address $address) {
        $netsuiteAddress = new ShipAddress();

        $lengthStreet1 = strlen(trim($address->getStreet1()));
        $lengthStreet2 = strlen(trim($address->getStreet2()));
        $street1 = substr(trim($address->getStreet1()), 0, 150);
        $sisa = "";
        if( $lengthStreet1 > 150 ) $sisa = substr(trim($address->getStreet1()), 150, $lengthStreet1);
        $street2 = substr(trim($sisa." ".$address->getStreet2()), 0, 150);

        $netsuiteAddress->shipAddr1 = $street1;//$address->getStreet1();
        $netsuiteAddress->shipAddr2 = $street2;//$address->getStreet2();
        $netsuiteAddress->shipCity = $address->getCity();
        $country = Mage::getModel('directory/country')->loadByCode($address->getCountry());
        $netsuiteAddress->shipCountry = Mage::helper('rocketweb_netsuite/transform')->transformCountryCode($country->getCountryId());
        $netsuiteAddress->shipAddressee = $address->getName();
        $netsuiteAddress->shipPhone = $address->getTelephone();
        $netsuiteAddress->shipState =  $address->getRegionCode();
        $netsuiteAddress->shipZip = $address->getPostcode();

        Mage::dispatchEvent('netsuite_ship_address_create_before',array('netsuite_address'=>$netsuiteAddress));

        return $netsuiteAddress;
    }

    public function getShippingAddressMagentoFormatFromNetsuiteAddress(ShipAddress $netsuiteShippingAddress,Customer $netsuiteCustomer = null,Mage_Sales_Model_Order $magentoOrder = null) {
        $magentoAddress = Mage::getModel('sales/order_address');
        $countryCode = Mage::helper('rocketweb_netsuite/transform')->netsuiteCountryToCountryCode($netsuiteShippingAddress->shipCountry);
        $regionId = Mage::helper('rocketweb_netsuite/transform')->regionCodeToRegionId($netsuiteShippingAddress->shipState,$countryCode);
        $magentoAddress->setRegionId($regionId);
        if($magentoOrder) {
            $magentoAddress->setParentId($magentoOrder->getId());
            $magentoAddress->setCustomerId($magentoOrder->getCustomerId());
        }
        if($regionId) {
            $regionName = Mage::helper('rocketweb_netsuite/transform')->regionCodeToRegionName($netsuiteShippingAddress->shipState,$countryCode);
        }
        else {
            $regionName = $netsuiteShippingAddress->shipState;
        }
        $magentoAddress->setRegion($regionName);
        $magentoAddress->setPostcode($netsuiteShippingAddress->shipZip);
        $magentoAddress->setCountryId($countryCode);

        $address = array($netsuiteShippingAddress->shipAddr1);
        if($netsuiteShippingAddress->shipAddr2) {
            $address[]=$netsuiteShippingAddress->shipAddr2;
        }
        if($netsuiteShippingAddress->shipAddr3) {
            $address[]=$netsuiteShippingAddress->shipAddr3;
        }
        $magentoAddress->setStreet($address);
        $magentoAddress->setCity($netsuiteShippingAddress->shipCity);
        $magentoAddress->setAddressType('shipping');

        if($netsuiteCustomer) {
            $magentoAddress->setLastname($netsuiteCustomer->lastName);
            $magentoAddress->setFirstname($netsuiteCustomer->firstName);
            $magentoAddress->setEmail($netsuiteCustomer->email);
            $magentoAddress->setTelephone($netsuiteCustomer->phone);
            $magentoAddress->setFax($netsuiteCustomer->fax);
            $magentoAddress->setMiddleName($netsuiteCustomer->middleName);
            $magentoAddress->setCompany($netsuiteCustomer->companyName);
        }

        if($netsuiteShippingAddress->shipAddressee) {
            $magentoAddress->setFirstname($netsuiteShippingAddress->shipAddressee);
            $magentoAddress->setLastname('');
        }

        return $magentoAddress;
    }

    public function getBillingAddressMagentoFormatFromNetsuiteAddress(BillAddress $netsuiteBillingAddress,Customer $netsuiteCustomer = null,Mage_Sales_Model_Order $magentoOrder = null) {
        $magentoAddress = Mage::getModel('sales/order_address');
        $countryCode = Mage::helper('rocketweb_netsuite/transform')->netsuiteCountryToCountryCode($netsuiteBillingAddress->billCountry);
        $regionId = Mage::helper('rocketweb_netsuite/transform')->regionCodeToRegionId($netsuiteBillingAddress->billState,$countryCode);
        $magentoAddress->setRegionId($regionId);
        if($magentoOrder) {
            $magentoAddress->setParentId($magentoOrder->getId());
            $magentoAddress->setCustomerId($magentoOrder->getCustomerId());
        }
        if($regionId) {
            $regionName = Mage::helper('rocketweb_netsuite/transform')->regionCodeToRegionName($netsuiteBillingAddress->billState,$countryCode);
        }
        else {
            $regionName = $netsuiteBillingAddress->billState;
        }
        $magentoAddress->setRegion($regionName);
        $magentoAddress->setPostcode($netsuiteBillingAddress->billZip);
        $magentoAddress->setCountryId($countryCode);


        $address = array($netsuiteBillingAddress->billAddr1);
        if($netsuiteBillingAddress->billAddr2) {
            $address[]=$netsuiteBillingAddress->billAddr2;
        }
        if($netsuiteBillingAddress->billAddr3) {
            $address[]=$netsuiteBillingAddress->billAddr3;
        }
        $magentoAddress->setStreet($address);
        $magentoAddress->setCity($netsuiteBillingAddress->billCity);
        $magentoAddress->setAddressType('billing');

        if($netsuiteCustomer) {
            $magentoAddress->setLastname($netsuiteCustomer->lastName);
            $magentoAddress->setFirstname($netsuiteCustomer->firstName);
            $magentoAddress->setEmail($netsuiteCustomer->email);
            $magentoAddress->setTelephone($netsuiteCustomer->phone);
            $magentoAddress->setFax($netsuiteCustomer->fax);
            $magentoAddress->setMiddleName($netsuiteCustomer->middleName);
            $magentoAddress->setCompany($netsuiteCustomer->companyName);
        }

        if($netsuiteBillingAddress->billAddressee) {
            $magentoAddress->setFirstname($netsuiteBillingAddress->billAddressee);
            $magentoAddress->setLastname('');
        }

        return $magentoAddress;
    }


}