<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Helper_Mapper_Customer extends RocketWeb_Netsuite_Helper_Mapper {

    public function getExternalId(Mage_Customer_Model_Customer $customer) {
        return $customer->getEmail();
    }
    public function getExternalIdFromOrder(Mage_Sales_Model_Order $order) {
        return $order->getCustomerEmail();
    }

    /**
     * @param Mage_Customer_Model_Customer $magentoCustomer
     * @return Customer
     */
    public function getNetsuiteFormat(Mage_Customer_Model_Customer $magentoCustomer) {

		$netsuiteCustomer = new Customer();
		
		$customFormId = Mage::getStoreConfig('rocketweb_netsuite/forms/customer_form_id');
		if($customFormId) {
			$netsuiteCustomer->customForm = new RecordRef();
			$netsuiteCustomer->customForm->internalId = $customFormId;
		}
		$netsuiteCustomer->externalId = $this->getExternalId($magentoCustomer);
		$netsuiteCustomer->entityId = $magentoCustomer->getEmail();
        if($this->getSetAltName()) {
            $netsuiteCustomer->altName = $magentoCustomer->getName();
        }
		$netsuiteCustomer->salutation = $magentoCustomer->getPrefix();
		$netsuiteCustomer->firstName = $magentoCustomer->getFirstname();
       		$firstName = $magentoCustomer->getFirstname();
        	$lastName = $magentoCustomer->getLastname();
		$netsuiteCustomer->lastName = !empty($lastName) ? $lastName : $firstName;
		$netsuiteCustomer->middleName = $magentoCustomer->getMiddlename();
		$netsuiteCustomer->phone = substr($magentoCustomer->getTelephone(), 0, 22);
		$fax = (string)preg_replace("/[^0-9]/","", $magentoCustomer->getFax());
		if($fax !== "" or !is_null($fax)){ while(strlen($fax) < 7){ $fax = $fax."+"; } }
		$netsuiteCustomer->fax = preg_replace("/[^0-9]/","", $fax);
		$netsuiteCustomer->email = $magentoCustomer->getEmail();
		$netsuiteCustomer->vatRegNumber = $magentoCustomer->getTaxvat();
		$netsuiteCustomer->stage = CustomerStage::_customer;
		$netsuiteCustomer->isPerson = true;
		
		$billingAddress = $magentoCustomer->getPrimaryBillingAddress();
		if($billingAddress) {
			$netsuiteCustomer->companyName = $billingAddress->getCompany();
			if(!$magentoCustomer->getTelephone()) {
				$netsuiteCustomer->phone = substr($billingAddress->getTelephone(), 0, 22);
			}
			if(!$magentoCustomer->getFax()) {
				$fax = (string)preg_replace("/[^0-9]/","", $billingAddress->getFax());
				if($fax !== "" or !is_null($fax)){ while(strlen($fax) < 7){ $fax = $fax."+"; } }
				$netsuiteCustomer->fax = $fax;
			}
				
		}

        $priceLevelInternalId = $this->getPriceLevelInternalId();
        if($priceLevelInternalId) {
            $netsuiteCustomer->priceLevel = new RecordRef();
            $netsuiteCustomer->priceLevel->internalId = $priceLevelInternalId;
            $netsuiteCustomer->priceLevel->type = RecordType::priceLevel;
        }
		
		$defaultBilling = $magentoCustomer->getDefaultBillingAddress();
		if(is_object($defaultBilling)) {
			$defaultBillingAddressId = $defaultBilling->getId();
		}
		else {
			$defaultBillingAddressId = null;
		}
		
		$defaultShipping = $magentoCustomer->getDefaultShippingAddress();
		if(is_object($defaultShipping)) {
			$defaultShippingAddressId = $defaultShipping->getId();
		}
		else {
			$defaultShippingAddressId = null;
		}
		
		$addresses = $magentoCustomer->getAddressesCollection();
		$netsuiteAddressList = new CustomerAddressbookList();
		$netsuiteAddressList->replaceAll = true;
		
		foreach($addresses as $magentoAddress) {
				
			$netsuiteAddress = new CustomerAddressbook();
				
			if($defaultShippingAddressId && $defaultShippingAddressId == $magentoAddress->getId()) {
				$netsuiteAddress->defaultShipping = true;
			}
			else {
				$netsuiteAddress->defaultShipping = false;
			}
				
			if($defaultBillingAddressId && $defaultBillingAddressId == $magentoAddress->getId()) {
				$netsuiteAddress->defaultBilling = true;
			}
			else {
				$netsuiteAddress->defaultBilling = false;
			}
				
			$netsuiteAddress->addressee = $magentoCustomer->getName();
			$netsuiteAddress->phone = substr($magentoAddress->getTelephone(), 0, 22);
            
            $lengthStreet1 = strlen(trim($magentoAddress->getStreet(1)));
            $lengthStreet2 = strlen(trim($magentoAddress->getStreet(2)));
            $street1 = substr(trim($magentoAddress->getStreet(1)), 0, 150);
            $sisa = "";
            if( $lengthStreet1 > 150 ) $sisa = substr(trim($magentoAddress->getStreet(1)), 151, $lengthStreet1);
            $street2 = substr(trim($sisa." ".$magentoAddress->getStreet(2)), 0, 150);

            //$netsuiteAddress->addr1 = $magentoAddress->getStreet(1);
            //$netsuiteAddress->addr2 = $magentoAddress->getStreet(2);
            $netsuiteAddress->addr1 = $street1;
            $netsuiteAddress->addr2 = $street2;
            $netsuiteAddress->city = $magentoAddress->getCity();
            $netsuiteAddress->zip = $magentoAddress->getPostcode();
            $netsuiteAddress->state = $magentoAddress->getRegionCode();
            $country = Mage::getModel('directory/country')->loadByCode($magentoAddress->getCountry());
            $netsuiteAddress->country = Mage::helper('rocketweb_netsuite/transform')->transformCountryCode($country->getCountryId());
            $netsuiteAddress->externalId = $magentoAddress->getId();

            Mage::dispatchEvent('netsuite_address_create_before',array('netsuite_address'=>$netsuiteAddress));
        
            $netsuiteAddressList->addressbook[]=$netsuiteAddress;

            $firstname = "firstname".$magentoAddress->getAddressType();
            $$firstname = $magentoAddress->getFirstname();

            $lastname = "lastname".$magentoAddress->getAddressType();
            $$lastname = $magentoAddress->getLastname();

            $custCollection = Mage::getModel('customer/customer')->load($magentoAddress->getCustomerId());

            $firstnameCustomer = $custCollection->getData('firstname');
            $lastnameCustomer = $custCollection->getData('lastname');

        }
        
        $netsuiteCustomer->addressbookList = $netsuiteAddressList;
        /*end moving up*/

        $netsuiteCustomer->salutation = $magentoCustomer->getPrefix();
        $firstName = $magentoCustomer->getFirstname();
        
        if (empty ($firstName)) {
            if (empty ($firstnamebilling)) {
                if (empty ($firstnameshipping)) {
                    if (empty ($firstnameCustomer)) {
                        $firstName = 'GUEST';
                    }
                    else {
                        $firstName = $firstnameCustomer;
                    }
                }
                else {
                    $firstName = $firstnameshipping;
                }
            }
            else{
                $firstName = $firstnamebilling;
            }
        }

        $lastName = $magentoCustomer->getLastname();
        
        if (empty ($lastName)) {
            if (empty ($lastnamebilling)) {
                if (empty ($lastnameshipping)){
                    if (empty ($lastnameCustomer)){
                        $lastName = 'GUEST';
                    }
                    else {
                        $lastName = $lastnameCustomer;
                    }
                }
                else {
                    $lastName = $lastnameshipping;
                }
            }
            else{
                $lastName = $lastnamebilling;
            }
        }

        $netsuiteCustomer->firstName = $firstName;
        $netsuiteCustomer->lastName = $lastName;
        $netsuiteCustomer->middleName = $magentoCustomer->getMiddlename();
        $netsuiteCustomer->phone = $magentoCustomer->getTelephone();
        $netsuiteCustomer->fax = $magentoCustomer->getFax();
        $netsuiteCustomer->email = $magentoCustomer->getEmail();
        $netsuiteCustomer->vatRegNumber = $magentoCustomer->getTaxvat();
        $netsuiteCustomer->stage = CustomerStage::_customer;
        $netsuiteCustomer->isPerson = true;
		
        return $netsuiteCustomer;
    }

    /**
     * Search a customer in netsuite,
     * for $by_field value look at RocketWeb_Netsuite_Helper_Mapper_Customer for parameters set in getNetsuiteFormat
     *
     * @param $by_field
     * @param $search_string
     * @return bool/string
     */
    public function findNetsuiteCustomer($by_field, $search_string) {

        $searchField = new SearchStringField();
        $searchField->operator = SearchStringFieldOperator::is;
        $searchField->searchValue = $search_string;
        $search = new CustomerSearchBasic();
        $search->$by_field = $searchField;

        $request = new SearchRequest();
        $request->searchRecord = $search;

        $netsuiteService = $this->_getNetsuiteService();
        $searchResponse = $netsuiteService->search($request);

        if(property_exists($searchResponse, 'searchResult') && property_exists($searchResponse->searchResult, 'totalRecords')
            && $searchResponse->searchResult->totalRecords != 0) {
            return $searchResponse->searchResult->recordList->record[0]->internalId;
        }

        return false;
    }

    /**
     * Returns a netsuite customer given an internal id
     *
     * @param string $internalId
     * @return Customer
     * @throws Exception
     */
    public function getByInternalId($internalId) {
        if(!$internalId) {
            return null;
        }
        $request = new GetRequest();
        $request->baseRef = new RecordRef();
        $request->baseRef->internalId = $internalId;
        $request->baseRef->type = RecordType::customer;

        $getResponse = $this->_getNetsuiteService()->get($request);
        if (!$getResponse->readResponse->status->isSuccess) {
            return null;
        }
        else {
            return $getResponse->readResponse->record;
        }
    }



    /**
     * Create a customer object with the id of zero then use it to get the customer in Netsuite format.
     * The customer object is never saved in Magento.
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     * @throws Exception
     */
    public function createNetsuiteCustomerFromOrder(Mage_Sales_Model_Order $order) {
        // Check if the email address already exists. We use 'entityId' instead of 'email' search field because it contains same email
        if ($internalId = $this->findNetsuiteCustomer('email', $this->getExternalIdFromOrder($order))) {
            return $internalId;
        }
        else {
            $customer = Mage::getModel('customer/customer');
            $customer->setId(0);
            $customer->setEmail($order->getCustomerEmail());
            $customer->setFirstname($order->getCustomerFirstname());
            $lastName = $order->getCustomerLastname();
            
            if (empty ($lastName)) {
                $lastName = $order->getCustomerFirstname();
            }
            
            $customer->setLastname($lastName);
            $customer->setMiddlename($order->getCustomerMiddlename());
            $customer->setPrimaryBillingAddress($order->getBillingAddress());
            $customer->setPrimaryShippingAddress($order->getShippingAddress());
            $customer->setStore($order->getStore());

            $billingAddr = Mage::getModel('customer/address')->setData($order->getBillingAddress()->getData());
            $customer->addAddress($billingAddr);

            $shippingAddr = Mage::getModel('customer/address')->setData($order->getShippingAddress()->getData());
            $customer->addAddress($shippingAddr);

            $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getNetsuiteFormat($customer);
            
            if ($netsuiteCustomer->externalId == 0) {
                $netsuiteCustomer->externalId = null;
            }

            Mage::dispatchEvent('netsuite_customer_send_before', array ('netsuite_customer' => $netsuiteCustomer));

            $request = new AddRequest();
            $request->record = $netsuiteCustomer;
            echo "netsuiteCustomer: " . json_encode($netsuiteCustomer) . "\n";
            exit;
            $response = $this->_getNetsuiteService()->add($request);
            
            if ($response->writeResponse->status->isSuccess) {
                return $response->writeResponse->baseRef->internalId;
            }
            else {
                throw new Exception((string) print_r($response->writeResponse->status->statusDetail, true));
            }
        }
    }

    protected function getPriceLevelInternalId() {
        return Mage::getStoreConfig('rocketweb_netsuite/customers/price_level');
    }

    protected function getSetAltName() {
        return Mage::getStoreConfig('rocketweb_netsuite/customers/set_alt_name');
    }
}