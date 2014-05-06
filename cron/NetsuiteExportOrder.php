<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once '../lib/Netsuite/NetSuiteService.php';
require_once '../app/Mage.php';

writeLog("Start export orders...");

Mage::app();
$netsuiteService = new NetSuiteService();

$magentoOrderCollection = getMagentoOrderCollection();
writeLog("Processing {$magentoOrderCollection->getSize()} orders ...");

foreach ($magentoOrderCollection as $magentoOrder) {
    if ($magentoOrder->getId()) {
        writeLog("Processing order #{$magentoOrder->getId()}");
    }
    
    $netsuiteOrder = getNetsuiteFormatOrder($netsuiteService, $magentoOrder);
    writeLog("netsuiteOrder: " . json_encode($netsuiteOrder));
    
    if (!$netsuiteOrder) {
        writeLog("Cannot get netsuiteOrder");
        continue;
    }
    
    Mage::dispatchEvent('netsuite_new_order_send_before', array ('magento_order' => $magentoOrder, 'netsuite_order' => $netsuiteOrder));
    
    $request = new AddRequest();
    $request->record = $netsuiteOrder;
    $response = $netsuiteService->add($request);
    writeLog("#" . $magentoOrder->getId() . " response: " . json_encode($response));

    if ($response->writeResponse->status->isSuccess) {
        $netsuiteId = $response->writeResponse->baseRef->internalId;
        $magentoOrder->setNetsuiteInternalId($netsuiteId);
        $magentoOrder->getResource()->save($magentoOrder);
        writeLog("Export success #" . $magentoOrder->getIncrementId());
    }
    else {
        writeLog("error export order: " . json_encode($response->writeResponse->status->statusDetail));
    }
}

writeLog("End export orders...");

function getMagentoOrderCollection() {
    $orderCollection = Mage::getModel('sales/order')->getCollection();
    $orderCollection->addAttributeToFilter('netsuite_internal_id', array ('eq' => ''));
    $orderCollection->addAttributeToFilter('entity_id', array ('lteq' => 1270));
    $orderCollection->addAttributeToSort('entity_id', 'DESC');
    $orderCollection->getSelect()->limit(1);
    
    return $orderCollection;
}

function getNetsuiteFormatOrder($netsuiteService, $magentoOrder) {
    $netsuiteCustomerId = getNetsuiteCustomerIdFromOrder($netsuiteService, $magentoOrder);
    
    if (!$netsuiteCustomerId) {
        writeLog("Could not find / create the netsuite customer externalIdString = " . $magentoOrder->getCustomerId());
        return false;
    }
    
    $netsuiteOrder = new SalesOrder();
    $netsuiteOrder->tranDate = new DateTime($magentoOrder->getCreatedAt());
    $netsuiteOrder->tranDate = $netsuiteOrder->tranDate->format(DateTime::ISO8601);
    
    if (getCanSetTranId()) {
        $netsuiteOrder->tranId = $magentoOrder->getIncrementId();
        writeLog("netsuiteOrder->tranId: " . $netsuiteOrder->tranId);
    }

    // Set customer record
    $netsuiteOrder->entity = new RecordRef();
    $netsuiteOrder->entity->type = RecordType::customer;
    $netsuiteOrder->entity->internalId = $netsuiteCustomerId;
    
    //set magento order id / increment id
    $netsuiteOrder->magentoOrderId = $magentoOrder->getIncrementId();

    $netsuiteOrder->orderStatus = orderStateMagentoToNetsuite($magentoOrder->getState());
    writeLog("netsuiteOrder->orderStatus: " . $netsuiteOrder->orderStatus);
    $fixedPriceBundles = array ();

    //taxes
    $taxItem = null;
    $taxInfo = $magentoOrder->getFullTaxInfo();
    
    if (is_array($taxInfo) && isset ($taxInfo[0])) {
        $rate = array_pop($taxInfo[0]['rates']);
        $taxRate = Mage::getModel('tax/calculation_rate')->getCollection()->addFieldToFilter('code', $rate['code'])->getFirstItem();
        $taxNetsuiteId = $taxRate->getNetsuiteInternalId();
        
        if ($taxNetsuiteId) {
            $taxItem = new RecordRef();
            $taxItem->type = RecordType::salesTaxItem;
            $taxItem->internalId = $taxNetsuiteId;
        }
    }
    
    $netsuiteOrderItems = array ();
    
    foreach ($magentoOrder->getAllItems() as $item) {
        if (in_array($item->getProductType(), array ('configurable'))) {
            continue;
        }
        
        $product = Mage::getModel('catalog/product')->load($item->getProductId());

        $netsuiteOrderItem = new SalesOrderItem();
        $netsuiteOrderItem->description = getProductDescription($item);
        $netsuiteOrderItem->quantity = $item->getQtyOrdered();
        $netsuiteOrderItem->quantityCommitted = $item->getQtyOrdered();
        $netsuiteOrderItem->item = new RecordRef();
        $netsuiteOrderItem->item->internalId = $product->getNetsuiteInternalId();
        $netsuiteOrderItem->price->internalId = -1;
        
        $netsuiteLocationId = Mage::helper('rocketweb_netsuite')->getNetsuiteLocationForStockDeduction();
        
        if ($netsuiteLocationId) {
            $netsuiteLocation = new RecordRef();
            $netsuiteLocation->type = RecordType::location;
            $netsuiteLocation->internalId = $netsuiteLocationId;
            $netsuiteOrderItem->location = $netsuiteLocation;
        }
        
        if (!((float) $item->getRowTotal()) && $item->getParentItemId()) {
            $parentItem = Mage::getModel('sales/order_item')->load($item->getParentItemId());
            $price = $parentItem->getRowTotal();
            $taxPercent = $parentItem->getTaxPercent();
        }
        else {
            $price = $item->getRowTotal();
            $taxPercent = $item->getTaxPercent();
        }

        if ($item->getProductType() == 'bundle') {
            if ($item->getProduct()->getPrice() == 0) {
                //We remove the price and tax here as a zero-priced bundle has the price of its parts.
                //Since we list the parts as simple products, the price will be doubled otherwise
                $price = 0;
            }
            else {
                //fixed price bundles. In this case, we must remove the price of the simple parts
                $fixedPriceBundles[$item->getId()] = true;
            }
        }
        
        if ($item->getProductType() == 'simple' && $item->getParentItemId() && isset ($fixedPriceBundles[$item->getParentItemId()])) {
            $price = 0;
        }
            
        $netsuiteOrderItem->amount = $price;
        $netsuiteOrderItem->rate = $price;
        
        if (!is_null($taxItem)) {
            $netsuiteOrderItem->taxCode = clone $taxItem;
            $netsuiteOrderItem->isTaxable = true;
            $netsuiteOrderItem->taxRate1 = $taxPercent;
            $netsuiteOrderItem->tax1Amt = $item->getTaxAmount();
        }
        else {
            $netsuiteOrderItem->taxCode = new RecordRef();
            $netsuiteOrderItem->taxCode->internalId = getNotTaxableInternalNetsuiteId();
            $netsuiteOrderItem->taxRate1 = 0;
            $netsuiteOrderItem->tax1Amt = 0;
        }
        
        $netsuiteOrderItems[] = $netsuiteOrderItem;
    }
    
    writeLog("netsuiteOrderItem: " . json_encode($netsuiteOrderItem));

    $netsuiteOrder->itemList = new SalesOrderItemList();
    $netsuiteOrder->itemList->item = $netsuiteOrderItems;

    //discount fields
    $discountSum = 0;
    //discount_amount is a sum of all discounts, including shipment & rewards
    $discountSum += (float) $magentoOrder->getDiscountAmount();
        
    if (Mage::helper('core')->isModuleEnabled('AW_Points')) {
        $pointsTransaction = Mage::getModel('points/transaction')->loadByOrder($magentoOrder);
        
        if ($pointsTransaction) {
            $discountSum += (float) $pointsTransaction->getData('base_points_to_money');
        }
    }
    
    if ($discountSum) {
        $netsuiteOrder->discountItem = new RecordRef();
        $netsuiteOrder->discountItem->internalId = getDiscountItemInternalNetsuiteId();
        $netsuiteOrder->discountItem->type = RecordType::discountItem;
        $netsuiteOrder->discountRate = $discountSum;
    }

    //addresses
    $netsuiteOrder->transactionBillAddress = getBillingAddressNetsuiteFormatFromOrderAddress($magentoOrder->getBillingAddress());
    $netsuiteOrder->transactionShipAddress = getShippingAddressNetsuiteFormatFromOrderAddress($magentoOrder->getShippingAddress());
    
    writeLog("netsuiteOrder->transactionBillAddress: " . json_encode($netsuiteOrder->transactionBillAddress));
    writeLog("netsuiteOrder->transactionShipAddress: " . json_encode($netsuiteOrder->transactionShipAddress));

    //shipping method
    $netsuiteShippingInternalId = getNetsuiteShippingMethodInternalId($magentoOrder->getShippingMethod());
    writeLog("netsuiteShippingInternalId: " . $netsuiteShippingInternalId);
    
    if (!is_null($netsuiteShippingInternalId)) {
        $netsuiteShippingMethod = new RecordRef();
        $netsuiteShippingMethod->internalId = $netsuiteShippingInternalId;
        $netsuiteOrder->shipMethod = $netsuiteShippingMethod;
        
        if (floatval($magentoOrder->getShippingTaxAmount())) {
            //shipping tax
            if (!is_null($taxItem)) {
                $netsuiteOrder->shippingTaxCode = clone $taxItem;
            }
        }
        else {
            //no tax for shipping
            $netsuiteOrder->shippingTaxCode = new RecordRef();
            $netsuiteOrder->shippingTaxCode->internalId = getNotTaxableInternalNetsuiteId();
        }
    }
    
    $netsuiteOrder->shippingCost = $magentoOrder->getShippingAmount();
    writeLog("netsuiteOrder->shippingCost: " . $netsuiteOrder->shippingCost);
    
    //payment method
    $paymentMethodNetsuiteId = getNetsuitePaymentMethodInternalId($magentoOrder->getPayment()->getMethodInstance()->getCode());
    writeLog("paymentMethodNetsuiteId: " . $paymentMethodNetsuiteId);
    
    if (!is_null($paymentMethodNetsuiteId)) {
        $netsuitePaymentMethod = new RecordRef();
        $netsuitePaymentMethod->type = RecordType::paymentMethod;
        $netsuitePaymentMethod->internalId = $paymentMethodNetsuiteId;
        $netsuiteOrder->paymentMethod = $netsuitePaymentMethod;
    }
    
    $paymentProcessor = getPaymentProcessor($magentoOrder);
    writeLog("paymentProcessor: " . $paymentProcessor);
        
    if ($paymentProcessor) {
        $paymentProcessorHelper = getPaymentProcessorHelper($magentoOrder);
        
        if (!is_null($paymentProcessorHelper)) {
            $netsuiteOrder = $paymentProcessorHelper->addProcessorSpecificInfromationToNetSuiteOrder($netsuiteOrder, $magentoOrder);
        }
    }
    
    //default fields
    $termsIs = getTermsId();
    writeLog("termsIs: " . $termsIs);
    
    if ($termsIs) {
        $netsuiteOrder->terms = new RecordRef();
        $netsuiteOrder->terms->type = RecordType::term;
        $netsuiteOrder->terms->internalId = $termsIs;
    }

    $classId = getClassId();
    writeLog("classId: " . $classId);
    
    if ($classId) {
        $netsuiteOrder->class = new RecordRef();
        $netsuiteOrder->class->type = RecordType::classification;
        $netsuiteOrder->class->internalId = $classId;
    }
    
    $departmentId = getDepartmentId();
    writeLog("departmentId: " . $departmentId);
    
    if ($departmentId) {
        $netsuiteOrder->department = new RecordRef();
        $netsuiteOrder->department->type = RecordType::department;
        $netsuiteOrder->department->internalId = $departmentId;
    }
    
    $salesRepId = getSalesRepId();
    writeLog("salesRepId: " . $salesRepId);
    
    if ($salesRepId) {
        $netsuiteOrder->salesRep = new RecordRef();
        $netsuiteOrder->salesRep->type = RecordType::employee;
        $netsuiteOrder->salesRep->internalId = $salesRepId;
    }
    
    $locationId = getLocationId();
    writeLog("locationId: " . $locationId);
    
    if ($locationId) {
        $netsuiteOrder->location = new RecordRef();
        $netsuiteOrder->location->type = RecordType::location;
        $netsuiteOrder->location->internalId = $locationId;
    }

    //custom fields
    $customFieldsConfig = getCustomFieldList();
    writeLog("customFieldsConfig: " . json_encode($customFieldsConfig));
    
    if (is_array($customFieldsConfig) && count($customFieldsConfig)) {
        $customFields = array ();
        
        foreach ($customFieldsConfig as $customFieldsConfigItem) {
            if ($customFieldsConfigItem['netsuite_field_type'] == 'standard') {
                $netsuiteOrder->{$customFieldsConfigItem['netsuite_field_name']} = _getCustomFieldValueFromMagentoData($customFieldsConfigItem, $magentoOrder);
            }
            else {
                $customField = _initCustomField($netsuiteService, $customFieldsConfigItem, $magentoOrder);
                $customFields[] = $customField;
            }
        }
        
        $netsuiteOrder->customFieldList = new CustomFieldList();
        $netsuiteOrder->customFieldList->customField = $customFields;
    }
    
    return $netsuiteOrder;
}

function getNetsuiteCustomerIdFromOrder($netsuiteService, $magentoOrder) {
    // Check if the email address already exists. We use 'entityId' instead of 'email' search field because it contains same email
    if ($customerInternalId = findNetsuiteCustomer($netsuiteService, 'email', getExternalIdFromOrder($magentoOrder))) {
        writeLog("Check if the email address already exists. We use 'entityId' instead of 'email' search field because it contains same email. customerInternalId: " . $customerInternalId);
        return $customerInternalId;
    }
    else {
        $customer = Mage::getModel('customer/customer');
        $customer->setId(0);
        $customer->setEmail($magentoOrder->getCustomerEmail());
        $customer->setFirstname($magentoOrder->getCustomerFirstname());
        $customer->setLastname($magentoOrder->getCustomerLastname());
        $customer->setMiddlename($magentoOrder->getCustomerMiddlename());
        $customer->setPrimaryBillingAddress($magentoOrder->getBillingAddress());
        $customer->setPrimaryShippingAddress($magentoOrder->getShippingAddress());
        $customer->setStore($magentoOrder->getStore());

        $billingAddr = Mage::getModel('customer/address')->setData($magentoOrder->getBillingAddress()->getData());
        $customer->addAddress($billingAddr);

        $shippingAddr = Mage::getModel('customer/address')->setData($magentoOrder->getShippingAddress()->getData());
        $customer->addAddress($shippingAddr);

        $netsuiteCustomer = getNetsuiteFormatCustomer($customer);
        
        if ($netsuiteCustomer->externalId == 0) {
            $netsuiteCustomer->externalId = null;
        }

        Mage::dispatchEvent('netsuite_customer_send_before', array ('netsuite_customer' => $netsuiteCustomer));

        $request = new AddRequest();
        $request->record = $netsuiteCustomer;
        $response = $netsuiteService->add($request);
        
        writeLog("responseAddcustomer: " . json_encode($response));
        
        if ($response->writeResponse->status->isSuccess) {
            writeLog("add customer (netsuite) success");
            return $response->writeResponse->baseRef->internalId;
        }
        else {
            writeLog("error export customer: " . json_encode($response->writeResponse->status->statusDetail));
            return false;
        }
    }
}

function findNetsuiteCustomer($netsuiteService, $by_field, $search_string) {
    $searchField = new SearchStringField();
    $searchField->operator = SearchStringFieldOperator::is;
    $searchField->searchValue = $search_string;
    $search = new CustomerSearchBasic();
    $search->$by_field = $searchField;

    $request = new SearchRequest();
    $request->searchRecord = $search;

    $searchResponse = $netsuiteService->search($request);

    if (property_exists($searchResponse, 'searchResult') && property_exists($searchResponse->searchResult, 'totalRecords') && $searchResponse->searchResult->totalRecords != 0) {
        writeLog("findNetsuiteCustomer: yes");
        return $searchResponse->searchResult->recordList->record[0]->internalId;
    }

    return false;
}

function getExternalId($magentoCustomer) {
    return $magentoCustomer->getEmail();
}

function getExternalIdFromOrder($magentoOrder) {
    return $magentoOrder->getCustomerEmail();
}

function getNetsuiteFormatCustomer($magentoCustomer) {
    $netsuiteCustomer = new Customer();
    $customFormId = Mage::getStoreConfig('rocketweb_netsuite/forms/customer_form_id');
    
    if ($customFormId) {
        $netsuiteCustomer->customForm = new RecordRef();
        $netsuiteCustomer->customForm->internalId = $customFormId;
    }
    
    $netsuiteCustomer->externalId = getExternalId($magentoCustomer);
    $netsuiteCustomer->entityId = $magentoCustomer->getEmail();
    
    if (getSetAltName()) {
        $netsuiteCustomer->altName = $magentoCustomer->getName();
    }
    
    $netsuiteCustomer->salutation = $magentoCustomer->getPrefix();
    $netsuiteCustomer->firstName = $magentoCustomer->getFirstname();
    $netsuiteCustomer->lastName = $magentoCustomer->getLastname();
    $netsuiteCustomer->middleName = $magentoCustomer->getMiddlename();
    $netsuiteCustomer->phone = $magentoCustomer->getTelephone();
    $netsuiteCustomer->fax = $magentoCustomer->getFax();
    $netsuiteCustomer->email = $magentoCustomer->getEmail();
    $netsuiteCustomer->vatRegNumber = $magentoCustomer->getTaxvat();
    $netsuiteCustomer->stage = CustomerStage::_customer;
    $netsuiteCustomer->isPerson = true;
    
    $billingAddress = $magentoCustomer->getPrimaryBillingAddress();
    
    if ($billingAddress) {
        $netsuiteCustomer->companyName = $billingAddress->getCompany();
        
        if (!$magentoCustomer->getTelephone()) {
            $netsuiteCustomer->phone = $billingAddress->getTelephone();
        }
        
        if (!$magentoCustomer->getFax()) {
            $netsuiteCustomer->fax = $billingAddress->getFax();
        }
    }
    
    $priceLevelInternalId = getPriceLevelInternalId();
    
    if ($priceLevelInternalId) {
        $netsuiteCustomer->priceLevel = new RecordRef();
        $netsuiteCustomer->priceLevel->internalId = $priceLevelInternalId;
        $netsuiteCustomer->priceLevel->type = RecordType::priceLevel;
    }
    
    $defaultBilling = $magentoCustomer->getDefaultBillingAddress();
    
    if (is_object($defaultBilling)) {
        $defaultBillingAddressId = $defaultBilling->getId();
    }
    else {
        $defaultBillingAddressId = null;
    }
		
    $defaultShipping = $magentoCustomer->getDefaultShippingAddress();
    
    if (is_object($defaultShipping)) {
        $defaultShippingAddressId = $defaultShipping->getId();
    }
    else {
        $defaultShippingAddressId = null;
    }
		
    $addresses = $magentoCustomer->getAddressesCollection();
    $netsuiteAddressList = new CustomerAddressbookList();
    $netsuiteAddressList->replaceAll = true;
		
    foreach ($addresses as $magentoAddress) {
        $netsuiteAddress = new CustomerAddressbook();
        
        if ($defaultShippingAddressId && $defaultShippingAddressId == $magentoAddress->getId()) {
            $netsuiteAddress->defaultShipping = true;
        }
        else {
            $netsuiteAddress->defaultShipping = false;
        }
        
        if ($defaultBillingAddressId && $defaultBillingAddressId == $magentoAddress->getId()) {
            $netsuiteAddress->defaultBilling = true;
        }
        else {
            $netsuiteAddress->defaultBilling = false;
        }
				
        $netsuiteAddress->addressee = $magentoCustomer->getName();
        $netsuiteAddress->phone = $magentoAddress->getTelephone();
        $netsuiteAddress->addr1 = $magentoAddress->getStreet(1);
        $netsuiteAddress->addr2 = $magentoAddress->getStreet(2);
        $netsuiteAddress->city = $magentoAddress->getCity();
        $netsuiteAddress->zip = $magentoAddress->getPostcode();
        $netsuiteAddress->state = $magentoAddress->getRegionCode();
        $country = Mage::getModel('directory/country')->loadByCode($magentoAddress->getCountry());
        $netsuiteAddress->country = Mage::helper('rocketweb_netsuite/transform')->transformCountryCode($country->getCountryId());
        $netsuiteAddress->externalId = $magentoAddress->getId();

        Mage::dispatchEvent('netsuite_address_create_before', array ('netsuite_address' => $netsuiteAddress));
		
        $netsuiteAddressList->addressbook[] = $netsuiteAddress;
    }
		
    $netsuiteCustomer->addressbookList = $netsuiteAddressList;
    
    return $netsuiteCustomer;
}

function getSetAltName() {
    return Mage::getStoreConfig('rocketweb_netsuite/customers/set_alt_name');
}

function getPriceLevelInternalId() {
    return Mage::getStoreConfig('rocketweb_netsuite/customers/price_level');
}

function getCanSetTranId() {
    return Mage::getStoreConfig('rocketweb_netsuite/orders/set_tran_id');
}

function orderStateMagentoToNetsuite($magentoOrderState) {
    $result = false;
    $statusToStateMap = Mage::getSingleton('rocketweb_netsuite/config')->convertDefaultMapOrderstatuses();
    writeLog("magentoOrderState: " . $magentoOrderState);
    writeLog("statusToStateMap: " . json_encode($statusToStateMap));
    
    foreach ($statusToStateMap as $statusToStateMapItem) {
        if ($statusToStateMapItem['magento_status'] == $magentoOrderState) {
            $result = $statusToStateMapItem['netsuite_status'];
            break;
        }
    }
    
    return $result;
}

function getProductDescription($item) {
    $description = $item->getName();
    $customOptions = $item->getProductOptions();
    
    if (is_array($customOptions) && isset ($customOptions['options']) && count($customOptions['options'])) {
        $customOptions = $customOptions['options'];
        $description .= ' - ';
        
        foreach ($customOptions as $option) {
            $description .= $option['label'] . ':' . $option['print_value'].', ';
        }
        
        $description = preg_replace('/, $/', '', $description);
    }

    return $description;
}

function getNotTaxableInternalNetsuiteId() {
    return Mage::getStoreConfig('rocketweb_netsuite/tax_rates/not_taxable_internal_netsuite_id');
}

function getDiscountItemInternalNetsuiteId() {
    return Mage::getStoreConfig('rocketweb_netsuite/orders/discount_item_id');
}

function getBillingAddressNetsuiteFormatFromOrderAddress($address) {
    $netsuiteAddress = new BillAddress();
    $netsuiteAddress->billAddr1 = $address->getStreet1();
    $netsuiteAddress->billAddr2 = $address->getStreet2();
    $netsuiteAddress->billCity = $address->getCity();
    $country = Mage::getModel('directory/country')->loadByCode($address->getCountry());
    $netsuiteAddress->billCountry = Mage::helper('rocketweb_netsuite/transform')->transformCountryCode($country->getCountryId());
    $netsuiteAddress->billAddressee = $address->getName();
    $netsuiteAddress->billPhone = $address->getTelephone();
    $netsuiteAddress->billState =  $address->getRegionCode();
    $netsuiteAddress->billZip = $address->getPostcode();

    Mage::dispatchEvent('netsuite_bill_address_create_before', array ('netsuite_address' => $netsuiteAddress));

    return $netsuiteAddress;
}

function getShippingAddressNetsuiteFormatFromOrderAddress($address) {
    $netsuiteAddress = new ShipAddress();
    $netsuiteAddress->shipAddr1 = $address->getStreet1();
    $netsuiteAddress->shipAddr2 = $address->getStreet2();
    $netsuiteAddress->shipCity = $address->getCity();
    $country = Mage::getModel('directory/country')->loadByCode($address->getCountry());
    $netsuiteAddress->shipCountry = Mage::helper('rocketweb_netsuite/transform')->transformCountryCode($country->getCountryId());
    $netsuiteAddress->shipAddressee = $address->getName();
    $netsuiteAddress->shipPhone = $address->getTelephone();
    $netsuiteAddress->shipState =  $address->getRegionCode();
    $netsuiteAddress->shipZip = $address->getPostcode();

    Mage::dispatchEvent('netsuite_ship_address_create_before', array ('netsuite_address' => $netsuiteAddress));

    return $netsuiteAddress;
}

function getNetsuiteShippingMethodInternalId($magentoShippingMethodCode) {
    $shippingMapping = unserialize(Mage::getStoreConfig('rocketweb_netsuite/shipping_methods/netsuite_mapping'));
    
    foreach ($shippingMapping as $shippingMappingElement) {
        if ($shippingMappingElement['shipping_method'] == $magentoShippingMethodCode) {
            return $shippingMappingElement['internal_netsuite_id'];
        }
    }

    return Mage::getStoreConfig('rocketweb_netsuite/shipping_methods/nesuite_default_shipping_id');
}

function getNetsuitePaymentMethodInternalId($magentoPaymentCode) {
    $paymentMapping = unserialize(Mage::getStoreConfig('rocketweb_netsuite/payment_methods/netsuite_mapping'));
    writeLog("paymentMapping: " . json_encode($paymentMapping));
    $result = null;
    
    foreach ($paymentMapping as $paymentMappingElement) {
        if ($paymentMappingElement['payment_method'] == $magentoPaymentCode) {
            if ($paymentMappingElement['payment_cc'] == '') {
                $result = $paymentMappingElement['internal_netsuite_id'];
                break;
            }
            else {
                if ($magentoPaymentObject->getCcType() == $paymentMappingElement['payment_cc']) {
                    $result = $paymentMappingElement['internal_netsuite_id'];
                    break;
                }
            }
        }
    }
    
    return $result;
}

function getPaymentProcessor($magentoOrder) {
    $paymentProcessorConfigItem = getPaymentProcessorConfigItem($magentoOrder);
    
    if (!is_null($paymentProcessorConfigItem)) {
        $paymentProcessor = new CustomRecordRef();
        $paymentProcessor->internalId = $paymentProcessorConfigItem['internal_netsuite_id'];
        
        return $paymentProcessor;
    }
    else {
        return null;
    }
}

function getPaymentProcessorHelper($magentoOrder) {
    $paymentProcessorConfigItem = getPaymentProcessorConfigItem($magentoOrder);
    writeLog("paymentProcessorConfigItem: " . json_encode($paymentProcessorConfigItem));
    
    if (!is_null($paymentProcessorConfigItem)) {
        $helper = Mage::helper('rocketweb_netsuite/paymentprocessors_' . $paymentProcessorConfigItem['payment_processor_helper_class']);
        return $helper;
    }
    else {
        return null;
    }
}

function getPaymentProcessorConfigItem($magentoOrder) {
    $paymentProcessorConfig = unserialize(Mage::getStoreConfig('rocketweb_netsuite/payment_methods/processor_mapping'));
    writeLog("paymentProcessorConfig: " . json_encode($paymentProcessorConfig));
    
    if (is_array($paymentProcessorConfig) && count($paymentProcessorConfig)) {
        foreach ($paymentProcessorConfig as $paymentProcessorConfigItem) {
            if ($paymentProcessorConfigItem['payment_method'] == $magentoOrder->getPayment()->getMethodInstance()->getCode()) {
                return $paymentProcessorConfigItem;
            }
        }
    }
    
    return null;
}

function getTermsId() {
    return Mage::getStoreConfig('rocketweb_netsuite/orders/terms_id');
}

function getClassId() {
    return Mage::getStoreConfig('rocketweb_netsuite/orders/class_id');
}

function getDepartmentId() {
    return Mage::getStoreConfig('rocketweb_netsuite/orders/department_id');
}

function getSalesRepId() {
    return Mage::getStoreConfig('rocketweb_netsuite/orders/sales_rep_id');
}

function getLocationId() {
    return Mage::getStoreConfig('rocketweb_netsuite/orders/location_id');
}

function getCustomFieldList() {
    return unserialize(Mage::getStoreConfig('rocketweb_netsuite/orders/custom_fields_mapping'));
}

function _getCustomFieldValueFromMagentoData($customFieldsConfigItem, $magentoOrder) {
    switch ($customFieldsConfigItem['value_type']) {
        case 'order_attribute':
            return $magentoOrder->getData($customFieldsConfigItem['value']);
        case 'fixed':
        default:
            return $customFieldsConfigItem['value'];
            break;
    }
}

function _initCustomField($netsuiteService, $customFieldsConfigItem, $magentoOrder) {
    switch ($customFieldsConfigItem['netsuite_field_type']) {
        case 'list':
            return _initListCustomField($netsuiteService, $customFieldsConfigItem, $magentoOrder);
        case 'simple':
        default:
            return _initSimpleCustomField($customFieldsConfigItem, $magentoOrder);
    }
}

function _initListCustomField($netsuiteService, $customFieldsConfigItem, $magentoOrder) {
    $customField = new SelectCustomFieldRef();
    $customField->internalId = $customFieldsConfigItem['netsuite_field_name'];

    $customList = _getCustomList($netsuiteService, $customFieldsConfigItem['netsuite_list_internal_id']);

    $recordRef = new ListOrRecordRef();
    $recordRef->typeId = $customList->internalId;
    
    foreach ($customList->customValueList->customValue as $customListCustomValue) {
        $value = _getCustomFieldValueFromMagentoData($customFieldsConfigItem,$magentoOrder);
        
        if (strtolower($value) == strtolower($customListCustomValue->value)) {
            $recordRef->internalId = $customListCustomValue->valueId;
            break;
        }
    }

   $customField->value = $recordRef;

   return $customField;
}

function _getCustomList($netsuiteService, $internalId) {
    $request = new GetRequest();
    $request->baseRef = new RecordRef();
    $request->baseRef->internalId = $internalId;
    $request->baseRef->type = RecordType::customList;

    $getResponse = $netsuiteService->get($request);
    
    if (!$getResponse->readResponse->status->isSuccess) {
        writeLog("error getCustomList: " . json_encode($getResponse->readResponse->status->statusDetail));
        
        return false;
    }
    else {
        return $getResponse->readResponse->record;
    }
}

function _initSimpleCustomField($customFieldsConfigItem, $magentoOrder) {
    $customField = new StringCustomFieldRef();
    $customField->internalId = $customFieldsConfigItem['netsuite_field_name'];
    $customField->value = _getCustomFieldValueFromMagentoData($customFieldsConfigItem, $magentoOrder);

    return $customField;
}

function writeLog($message) {
    @error_log($message . "\n", 3, "/tmp/netsuiteExportOrder.log");
}
