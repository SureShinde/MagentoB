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

class RocketWeb_Netsuite_Helper_Mapper_Order extends RocketWeb_Netsuite_Helper_Mapper {
    private $_customPriceLevelId = 0;

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return SalesOrder
     * @throws Exception
     */
    public function getNetsuiteFormat(Mage_Sales_Model_Order $magentoOrder) {
        // Lookup customer_id / create if does not exists
        $netsuiteCustomerId = Mage::helper('rocketweb_netsuite/mapper_customer')->createNetsuiteCustomerFromOrder($magentoOrder);

        if (!$netsuiteCustomerId) {
            throw new Exception("Could not find / create the netsuite customer externalIdString=". $magentoOrder->getCustomerId());
        }

        $netsuiteOrder = new SalesOrder();
        $netsuiteOrder->tranDate = new DateTime($magentoOrder->getCreatedAt());
        $netsuiteOrder->tranDate = $netsuiteOrder->tranDate->format(DateTime::ISO8601);
        if($this->getCanSetTranId()) {
            $netsuiteOrder->tranId = $magentoOrder->getIncrementId();
        }

        // Set customer record
        $netsuiteOrder->entity = new RecordRef();
        $netsuiteOrder->entity->type = RecordType::customer;
        $netsuiteOrder->entity->internalId = $netsuiteCustomerId;


        $netsuiteOrder->orderStatus = $this->getOrderStatusMap($magentoOrder->getState());
        //$netsuiteOrder->orderStatus = Mage::helper('rocketweb_netsuite/transform')->orderStateMagentoToNetsuite($magentoOrder->getState());
        $fixedPriceBundles = array();

        //taxes
        $taxItem = null;
        $taxInfo = $magentoOrder->getFullTaxInfo();
        if(is_array($taxInfo) && isset($taxInfo[0])) {
            $rate = array_pop($taxInfo[0]['rates']);
            $taxRate = Mage::getModel('tax/calculation_rate')->getCollection()->addFieldToFilter('code',$rate['code'])->getFirstItem();
            $taxNetsuiteId = $taxRate->getNetsuiteInternalId();
            if($taxNetsuiteId) {
                $taxItem = new RecordRef();
                $taxItem->type = RecordType::salesTaxItem;
                $taxItem->internalId = $taxNetsuiteId;
            }
        }

        $customFieldsConfig = $this->getCustomFieldList();
        $netsuiteOrderItems = array ();
       
        $confProduct = array();
        $bundProduct = array();
        foreach ($magentoOrder->getAllItems() as $item) {
            if ( $item->getProductType() == 'configurable' ) {
                $confProduct[$item->getData('item_id')] = $item->getData('price');
                $confProductDiscount[$item->getData('item_id')] = $item->getData('discount_amount');
            }elseif( $item->getProductType() == 'bundle' ){
                $parentItemId = $item->getData('parent_item_id');
                if( $parentItemId == '' || $parentItemId == 0 || empty($parentItemId) ){
                    $parentItemId = $item->getData('item_id');
                } 
                $productOptions = unserialize($item->getData('product_options'));
                $bundleOptions = $productOptions['bundle_options'];
                $totalPriceItemBundle = 0;
                foreach($bundleOptions as $option){
                    foreach($option['value'] as $value){
                        $totalPriceItemBundle += $value['price'];
                    }
                }

                $bundProduct[$item->getData('item_id')][$parentItemId]['price'] = $item->getData('price');
                $bundProduct[$item->getData('item_id')][$parentItemId]['qty']   = $item->getData('qty_ordered');
                $bundProduct[$item->getData('item_id')]['totalQtyBundle']   = $item->getData('qty_ordered');
                $bundProduct[$item->getData('item_id')]['priceBundle']   = $item->getData('price') - $totalPriceItemBundle;
                $bundProduct[$item->getData('item_id')]['priceBundle2']   = $item->getData('price');
                $bundProduct[$item->getData('item_id')]['discountAmount']   = $item->getData('discount_amount');
            }elseif( isset($bundProduct[$item->getData('parent_item_id')]) ){
                $bundProduct[$item->getData('parent_item_id')]['totalQty']   += $item->getData('qty_ordered');
                $bundProduct[$item->getData('parent_item_id')][$item->getData('item_id')]['itemQty']   = $item->getData('qty_ordered');
            }
        }

        foreach ($magentoOrder->getAllItems() as $item) {        

            if (in_array($item->getProductType(), array ('configurable', 'bundle'))) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            $netsuiteOrderItem = new SalesOrderItem();
            $netsuiteOrderItem->description = Mage::helper('rocketweb_netsuite/mapper_product')->getProductDescription($item);
            $netsuiteOrderItem->quantity = $item->getQtyOrdered();
            $netsuiteOrderItem->quantityCommitted = $item->getQtyOrdered();
            $netsuiteOrderItem->item = new RecordRef();
            $netsuiteOrderItem->item->internalId = $product->getNetsuiteInternalId() ? $product->getNetsuiteInternalId() : $this->getProductDefaultInternalId();
            $netsuiteOrderItem->price->internalId = -1;
            
            //set item status
            //if ($netsuiteOrder->orderStatus == '_closed') {
            //    $netsuiteOrderItem->isClosed = true;
            //}

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
            
            //$netsuiteOrderItem->amount = $price;
            
            if (!is_null($taxItem)) {
                $netsuiteOrderItem->taxCode = clone $taxItem;
                $netsuiteOrderItem->isTaxable = true;
                $netsuiteOrderItem->taxRate1 = $taxPercent;
                $netsuiteOrderItem->tax1Amt = $item->getTaxAmount();
            }
            else {
                $netsuiteOrderItem->taxCode = new RecordRef();
                $netsuiteOrderItem->taxCode->internalId = $this->getNotTaxableInternalNetsuiteId();
                $netsuiteOrderItem->taxRate1 = 0;
                $netsuiteOrderItem->tax1Amt = 0;
            }

            //custom fields item
            if (Mage::helper('core')->isModuleEnabled('AW_Points')) {
                $pointsTransaction = Mage::getModel('points/transaction')->loadByOrder($magentoOrder);
            }
            
            $priceBeforeDiscount = 0;
            
            if (is_array($customFieldsConfig) && count($customFieldsConfig)) {
                $customFields = array ();
                $totalDiscount = 0;
                $productOptions = unserialize($item->getData('product_options'));
                if( $item->getProductType() == 'simple' && $item->getData('price') == 0 || $item->getData('parent_item_id') != '' ){
                    if( isset( $productOptions['bundle_selection_attributes'] ) ){
                        $bundleSelectionAttributes = unserialize($productOptions['bundle_selection_attributes']);
                        $priceItemOnBundle = $bundleSelectionAttributes['price'] / $bundleSelectionAttributes['qty'];
                        //$bundlePrice = $bundProduct[$item->getData('parent_item_id')][''] 
                        //$priceItemOnBundle = $bundProduct[$item->getData('parent_item_id')][$item->getData('item_id')]['price'];
                        $pricePerBundle = $bundProduct[$item->getData('parent_item_id')]['priceBundle'] * $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                        //$pricePerBundle = $bundProduct[$item->getData('parent_item_id')]['priceBundle'];
//                        $pricePerBundle = $bundProduct[$item->getData('parent_item_id')][$item->getData('parent_item_id')]['price'] / $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                        $totalQty = $bundProduct[$item->getData('parent_item_id')]['totalQty'];
                        $finalPriceItem = $priceItemOnBundle + ( $pricePerBundle / $totalQty ) ;
                        //$item->setData('price', $finalPriceItem);
                    }
                    if(!empty($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0 ){
                        $finalPriceItem = $confProduct[$item->getData('parent_item_id')];
                    }
                }

                foreach ($customFieldsConfig as $customFieldsConfigItem) {
                    
                    switch ($customFieldsConfigItem['netsuite_field_name']) {
                        case 'custcol_discountitem':
                            if (in_array($item->getProductType(), array ('bundle'))) {
                                continue;
                            }
                            $bundleDiscount = 0;
                            $discountPerItem = 0;
                            if( (isset( $productOptions['bundle_selection_attributes'] )) && ($item->getProductType() == 'simple' && $item->getData('price') == 0 || $item->getData('parent_item_id') != '') ){
                                $discountAmount = $bundProduct[$item->getData('parent_item_id')]['discountAmount'];
                                $priceBundle = $bundProduct[$item->getData('parent_item_id')]['priceBundle2'] * $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                                //$totalQty = $bundProduct[$item->getData('parent_item_id')]['totalQty'];
                                //$totalQtyBundle = $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];

                                $bundleDiscount = (($discountAmount / $priceBundle) * $finalPriceItem );

                            }
                            if( isset($confProductDiscount[$item->getData('parent_item_id')]) ){
                                $disc = $confProductDiscount[$item->getData('parent_item_id')];
                                $discountPerItem = - (float) (round(($disc / $item->getData('qty_ordered')),3));
                            }else{
                                $discountPerItem = - (float) ($bundleDiscount + round(($item->getData('discount_amount') / $item->getData('qty_ordered')),3));
                            }

                            $totalDiscount += $discountPerItem;
                            $item->setData('discount_amount', $discountPerItem);
                            $customField = $this->_initCustomField($customFieldsConfigItem, $item);
                            $customFields[] = $customField;
                            break;
                        case 'custcol_bilnacredit':
                            if (in_array($item->getProductType(), array ('bundle'))) {
                                continue;
                            }

                            if( $item->getProductType() == 'simple' && $item->getData('price') == 0 || $item->getData('parent_item_id') != '' ){
                                if( isset( $productOptions['bundle_selection_attributes'] ) ){
                                    $bilna_credit = $pointsTransaction->getData('base_points_to_money');
                                    $subTotal = $magentoOrder->getSubtotal();
                                    $bilnaCreditItem = $finalPriceItem * ($bilna_credit / $subTotal);
                                }
                                if(!empty($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0 ){                                
                                    $bilnaCreditItem = $confProduct[$item->getData('parent_item_id')] * ($bilna_credit / $subTotal);
                                }
                            }else{
                                $bilna_credit = $pointsTransaction->getData('base_points_to_money');
                                $subTotal = $magentoOrder->getSubtotal();
                                $bilnaCreditItem = $item->getData('price') * ($bilna_credit / $subTotal);
                            }
                            
                            $pointsTransaction->setData('base_points_to_money', $bilnaCreditItem);
                            $customField = $this->_initCustomField($customFieldsConfigItem, $pointsTransaction);
                            $customFields[] = $customField;
                            $totalDiscount += $bilnaCreditItem;
                            break;
                        case 'custcol_pricebeforediscount':

                            if( $item->getProductType() == 'bundle' ){
                                $item->setData('price', 0);
                            }elseif( $item->getProductType() == 'simple' && $item->getData('price') == 0 ){
                                if( isset( $productOptions['bundle_selection_attributes'] ) ){
                                    
                                    $item->setData('price', $finalPriceItem);
                                }
                                if(!empty($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0 ){
                                    $item->setData('price', $confProduct[$item->getData('parent_item_id')]);
                                }
                            }

                            $priceBeforeDiscount = $this->_getCustomFieldValueFromMagentoData($customFieldsConfigItem, $item);
                            $customField = $this->_initCustomField($customFieldsConfigItem, $item);
                            $customFields[] = $customField;
                            break;
                    }
                }

                $netsuiteOrderItem->customFieldList = new CustomFieldList();
                $netsuiteOrderItem->customFieldList->customField = $customFields;
            }
            
            if(!empty($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0 ){
                $priceBeforeDiscount = $confProduct[$item->getData('parent_item_id')];
            }           
            if($item->getProductType() == 'bundle'){
                //$priceBeforeDiscount = 0;               
                $netsuiteOrderItem->rate = 0;
            }else{
                $netsuiteOrderItem->rate = round($priceBeforeDiscount + $totalDiscount, 3);
            }
            $netsuiteOrderItems[] = $netsuiteOrderItem;
        }

        $netsuiteOrder->itemList = new SalesOrderItemList();
        $netsuiteOrder->itemList->item = $netsuiteOrderItems;

        //discount fields
        $discountSum = 0;
        //discount_amount is a sum of all discounts, including shipment & rewards
        $discountSum+= (float)$magentoOrder->getDiscountAmount();
        if(Mage::helper('core')->isModuleEnabled('AW_Points')) {
            $pointsTransaction = Mage::getModel('points/transaction')->loadByOrder($magentoOrder);
            if($pointsTransaction) {
                $discountSum+= (float) $pointsTransaction->getData('base_points_to_money');
            }
        }



        //if ($discountSum) {
        //    $netsuiteOrder->discountItem = new RecordRef();
        //    $netsuiteOrder->discountItem->internalId = $this->getDiscountItemInternalNetsuiteId();
        //    $netsuiteOrder->discountItem->type = RecordType::discountItem;
        //    $netsuiteOrder->discountRate = 0;
        //}


        //addresses
        $netsuiteOrder->transactionBillAddress = Mage::helper('rocketweb_netsuite/mapper_address')->getBillingAddressNetsuiteFormatFromOrderAddress($magentoOrder->getBillingAddress());
        $netsuiteOrder->transactionShipAddress = Mage::helper('rocketweb_netsuite/mapper_address')->getShippingAddressNetsuiteFormatFromOrderAddress($magentoOrder->getShippingAddress());


        //shipping method
        $netsuiteShippingInternalId = Mage::helper('rocketweb_netsuite')->getNetsuiteShippingMethodInternalId($magentoOrder->getShippingMethod());
        if(!is_null($netsuiteShippingInternalId)) {
            $netsuiteShippingMethod = new RecordRef();
            $netsuiteShippingMethod->internalId = $netsuiteShippingInternalId;
            $netsuiteOrder->shipMethod = $netsuiteShippingMethod;


            if(floatval($magentoOrder->getShippingTaxAmount())) {
                //shipping tax
                if(!is_null($taxItem)) {
                   $netsuiteOrder->shippingTaxCode = clone $taxItem;
                }
            }
            else {
                //no tax for shipping
                $netsuiteOrder->shippingTaxCode = new RecordRef();
                $netsuiteOrder->shippingTaxCode->internalId = $this->getNotTaxableInternalNetsuiteId();

            }
        }
        $netsuiteOrder->shippingCost = $magentoOrder->getShippingAmount();


        //payment method
        $paymentMethodNetsuiteId = Mage::helper('rocketweb_netsuite')->getNetsuitePaymentMethodInternalId($magentoOrder->getPayment());
        if(!is_null($paymentMethodNetsuiteId)) {
            $netsuitePaymentMethod = new RecordRef();
            $netsuitePaymentMethod->type = RecordType::paymentMethod;
            $netsuitePaymentMethod->internalId = $paymentMethodNetsuiteId;
            $netsuiteOrder->paymentMethod = $netsuitePaymentMethod;
        }


        $paymentProcessor = $this->getPaymentProcessor($magentoOrder);
        if($paymentProcessor) {
            //$netsuiteOrder->creditCardProcessor = $paymentProcessor;
            $paymentProcessorHelper = $this->getPaymentProcessorHelper($magentoOrder);
            if(!is_null($paymentProcessorHelper)) {
                $netsuiteOrder = $paymentProcessorHelper->addProcessorSpecificInfromationToNetSuiteOrder($netsuiteOrder,$magentoOrder);
            }
        }



        //default fields
        $termsIs = $this->getTermsId();
        if($termsIs) {
            $netsuiteOrder->terms = new RecordRef();
            $netsuiteOrder->terms->type = RecordType::term;
            $netsuiteOrder->terms->internalId = $termsIs;
        }

        $classId = $this->getClassId();
        if($classId) {
            $netsuiteOrder->class = new RecordRef();
            $netsuiteOrder->class->type = RecordType::classification;
            $netsuiteOrder->class->internalId = $classId;
        }
        $departmentId = $this->getDepartmentId();
        if($departmentId) {
            $netsuiteOrder->department = new RecordRef();
            $netsuiteOrder->department->type = RecordType::department;
            $netsuiteOrder->department->internalId = $departmentId;
        }
        $salesRepId = $this->getSalesRepId();
        if($salesRepId) {
            $netsuiteOrder->salesRep = new RecordRef();
            $netsuiteOrder->salesRep->type = RecordType::employee;
            $netsuiteOrder->salesRep->internalId = $salesRepId;
        }
        $locationId = $this->getLocationId();
        if($locationId) {
            $netsuiteOrder->location = new RecordRef();
            $netsuiteOrder->location->type = RecordType::location;
            $netsuiteOrder->location->internalId = $locationId;
        }
        
        if (is_array($customFieldsConfig) && count($customFieldsConfig)) {
            $customFields = array ();
            
            foreach ($customFieldsConfig as $customFieldsConfigItem) {
                if ($customFieldsConfigItem['netsuite_field_type'] == 'standard') {
                    $netsuiteOrder->{$customFieldsConfigItem['netsuite_field_name']} = $this->_getCustomFieldValueFromMagentoData($customFieldsConfigItem, $magentoOrder);
                }
                else {
                    if (in_array($customFieldsConfigItem['netsuite_field_name'], array ('custcol_discountitem', 'custcol_bilnacredit', 'custcol_pricebeforediscount'))) {
                        continue;
                    }
                    elseif ($customFieldsConfigItem['netsuite_field_name'] == 'custbody_paymentmethod') {
                        $customPaymentMethod = new StringCustomFieldRef();
                        $customPaymentMethod->internalId = $customFieldsConfigItem['netsuite_field_name'];
                        $customPaymentMethod->value = $paymentMethodNetsuiteId;
                        $customFields[] = $customPaymentMethod;
                    }
                    elseif ($customFieldsConfigItem['netsuite_field_name'] == 'custbody_magentostatus') {
                        if ($orderHistorical = $this->getOrderHistorical()) {
                            $customOrderHistorical = new StringCustomFieldRef();
                            $customOrderHistorical->internalId = 'custbody_magentohistorical';
                            //$customOrderHistorical->value = 'F';
                            $customOrderHistorical->value = $orderHistorical ? 'T' : 'F';
                            $customFields[] = $customOrderHistorical;
                            
                            $customMagentoStatus = new StringCustomFieldRef();
                            $customMagentoStatus->internalId = $customFieldsConfigItem['netsuite_field_name'];
                            $customMagentoStatus->value = $magentoOrder->getStatusLabel();
                            $customFields[] = $customMagentoStatus;
                        }
                    }
                    else {
                        $customField = $this->_initCustomField($customFieldsConfigItem, $magentoOrder);
                        $customFields[] = $customField;
                    }
                }
            }
            
            $netsuiteOrder->customFieldList = new CustomFieldList();
            $netsuiteOrder->customFieldList->customField = $customFields;
        }

        return $netsuiteOrder;
    }

    protected function _initCustomField($customFieldsConfigItem,$magentoOrder) {
        switch($customFieldsConfigItem['netsuite_field_type']) {
            case 'list':
                return $this->_initListCustomField($customFieldsConfigItem,$magentoOrder);
            case 'simple':
            default:
                return $this->_initSimpleCustomField($customFieldsConfigItem,$magentoOrder);
        }
    }

    protected function _initListCustomField($customFieldsConfigItem,$magentoOrder) {
        $customField = new SelectCustomFieldRef();
        $customField->internalId = $customFieldsConfigItem['netsuite_field_name'];

        $customList = $this->_getCustomList($customFieldsConfigItem['netsuite_list_internal_id']);

        $recordRef = new ListOrRecordRef();
        $recordRef->typeId = $customList->internalId;
        foreach($customList->customValueList->customValue as $customListCustomValue) {
            $value = $this->_getCustomFieldValueFromMagentoData($customFieldsConfigItem,$magentoOrder);
            if(strtolower($value) == strtolower($customListCustomValue->value)) {
                $recordRef->internalId = $customListCustomValue->valueId;
                break;
            }
       }

       $customField->value = $recordRef;

       return $customField;
    }

    protected function _initSimpleCustomField($customFieldsConfigItem,$magentoOrder) {
        $customField = new StringCustomFieldRef();
        $customField->internalId = $customFieldsConfigItem['netsuite_field_name'];

        if ($customFieldsConfigItem['netsuite_field_name'] == 'custbody_deliverytype') {
        	$customField->value = str_replace("Pilih - ", "", $this->_getCustomFieldValueFromMagentoData($customFieldsConfigItem,$magentoOrder));
        }else{
        	$customField->value = $this->_getCustomFieldValueFromMagentoData($customFieldsConfigItem,$magentoOrder);
        }

        return $customField;
    }

    protected function _getCustomFieldValueFromMagentoData($customFieldsConfigItem,$magentoOrder) {
        switch($customFieldsConfigItem['value_type']) {
            case 'order_attribute':
                return $magentoOrder->getData($customFieldsConfigItem['value']);
            case 'fixed':
            default:
                return $customFieldsConfigItem['value'];
                break;
        }
    }

    protected function _getCustomList($internalId) {

        $request = new GetRequest();
        $request->baseRef = new RecordRef();
        $request->baseRef->internalId = $internalId;
        $request->baseRef->type = RecordType::customList;

        $getResponse = Mage::helper('rocketweb_netsuite')->getNetsuiteService()->get($request);
		echo "_getCustomList request: " . json_encode($request) . "\n";
		echo "_getCustomList response: " . json_encode($getResponse) . "\n";
        if (!$getResponse->readResponse->status->isSuccess) {
            throw new Exception((string) print_r($getResponse->readResponse->status->statusDetail,true));
        }
        else {
            return $getResponse->readResponse->record;
        }

    }

    public function getMagentoFormat(SalesOrder $netsuiteOrder) {
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrder->internalId);
        
        if (!$magentoOrders->count()) {
            throw new Exception("Order with internal Netsuite id #{$netsuiteOrder->internalId} not found!");
        }
        
        $magentoOrder = $magentoOrders->getFirstItem();
        $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getByInternalId($netsuiteOrder->entity->internalId);
        $magentoOrderState = Mage::helper('rocketweb_netsuite/transform')->netsuiteStatusToMagentoOrderState($netsuiteOrder->orderStatus);
        
        /**
         * check netsuite order status
         */
        if ($magentoOrderState == 'canceled' || $magentoOrderState == 'closed') {
            /**
             * change state closed to canceled
             */
            if ($magentoOrderState == 'closed') {
                $magentoOrderState = 'canceled';
            }

            //re-save the billing and shipping addresses
            //if(!$magentoOrder->hasInvoices()) {
            //    if($netsuiteOrder->transactionBillAddress) {
            //        $magentoBillingAddress = Mage::helper('rocketweb_netsuite/mapper_address')->getBillingAddressMagentoFormatFromNetsuiteAddress($netsuiteOrder->transactionBillAddress,
            //            $netsuiteCustomer,
            //            $magentoOrder);
            //        $magentoBillingAddress->setId($magentoOrder->getBillingAddressId());
            //        $magentoBillingAddress->save();
            //    }
            //}


            //if(!$magentoOrder->hasShipments()) {
            //    if($netsuiteOrder->transactionShipAddress) {
            //        $magentoShippingAddress = Mage::helper('rocketweb_netsuite/mapper_address')->getShippingAddressMagentoFormatFromNetsuiteAddress($netsuiteOrder->transactionShipAddress,
            //            $netsuiteCustomer,
            //            $magentoOrder);
            //        $magentoShippingAddress->setId($magentoOrder->getShippingAddressId());
            //        $magentoShippingAddress->save();
            //    }
            //}



            /**
             * Re-save the shipping method, but only if there are no shipments. This is because Net Suite allows storing different shipping data at order and shipment level.
             * In case item fulfillments are defined in Net Suite, they will be controlling the order.
             */
            if (!$magentoOrder->hasShipments() && is_object($netsuiteOrder->shipMethod)) {
                //$magentoOrder->setShippingDescription($netsuiteOrder->shipMethod->name);
                $magentoOrder->setShippingAmount($netsuiteOrder->shippingCost);
                $magentoOrder->setBaseShippingAmount($netsuiteOrder->shippingCost);
            }

            if (!is_null($magentoOrderState)) {
                $magentoOrder->setData('state', $magentoOrderState);
                $magentoOrder->setStatus($this->getStatusForState($magentoOrderState)->getStatus());
                $magentoOrder->addStatusHistoryComment('Net Suite status change', $magentoOrderState);
                $magentoOrder->getStatusHistoryCollection()->save();
            }

            //save changes to quantity & prices for each order item
            $itemMap = array ();

            foreach ($netsuiteOrder->itemList->item as $netsuiteOrderItem) {
                $found = false;

                foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
                    if (Mage::getModel('catalog/product')->load($magentoOrderItem->getProductId())->getNetsuiteInternalId() == $netsuiteOrderItem->item->internalId) {
                        $itemMap[] = array ('netsuiteItem' => $netsuiteOrderItem, 'magentoItem' => $magentoOrderItem);
                        $found = true;
                    }
                }

                if (!$found) {
                    //new line item added in Net Suite
                    $itemMap[] = array ('netsuiteItem' => $netsuiteOrderItem,'magentoItem' => null);
                }
            }

            $_netsuiteSubTotal = 0;

            foreach ($itemMap as $orderMapItem) {
                /**
                 * skip if item is wrapping
                 */
                if (preg_match('/^(\wrapping)/', $orderMapItem['netsuiteItem']->item->name)) {
                    continue;
                }

                if (is_null($orderMapItem['magentoItem'])) {
                    $orderMapItem['magentoItem'] = Mage::getModel('sales/order_item');
                    $orderMapItem['magentoItem']->setOrderId($magentoOrder->getId());
                    $orderMapItem['magentoItem']->setStoreId($magentoOrder->getStoreId());
                    $orderMapItem['magentoItem']->setProductType('simple');
                    $orderMapItem['magentoItem']->setSku($orderMapItem['netsuiteItem']->item->name);
                    $orderMapItem['magentoItem']->setName($orderMapItem['netsuiteItem']->description);
                }

                $orderMapItem['magentoItem']->setQtyOrdered($orderMapItem['netsuiteItem']->quantity);
                $_netsuitePrice = $this->getNetsuitePrice($orderMapItem['netsuiteItem']->customFieldList->customField);
                $_netsuiteRowTotal = $_netsuitePrice * $orderMapItem['netsuiteItem']->quantity;
                $_netsuiteSubTotal += $_netsuiteRowTotal;
                $orderMapItem['magentoItem']->setPrice($_netsuitePrice);
                $orderMapItem['magentoItem']->setBasePrice($_netsuitePrice);
                $orderMapItem['magentoItem']->setTaxPercent($orderMapItem['netsuiteItem']->taxRate1);
                $orderMapItem['magentoItem']->setRowTotal($_netsuiteRowTotal);
                $orderMapItem['magentoItem']->setBaseRowTotal($_netsuiteRowTotal);
                $taxAmount = round($orderMapItem['netsuiteItem']->taxRate1 / 100 * $_netsuiteRowTotal);
                $orderMapItem['magentoItem']->setTaxAmount($taxAmount);
                $orderMapItem['magentoItem']->setBaseTaxAmount($taxAmount);
                $orderMapItem['magentoItem']->setQtyCanceled($orderMapItem['netsuiteItem']->quantity);
                $orderMapItem['magentoItem']->save();
            }

            //check if any order item was deleted.
            $validOrderItemIds = array ();
            //create an array with all order item ids that still have a Netsuite correspondent
            foreach ($itemMap as $orderMapItem) {
                if ($orderMapItem['magentoItem']) {
                    $validOrderItemIds[] = $orderMapItem['magentoItem']->getItemId();
                }
            }

            //since we never send configurables to netsuite, we never need to delete them
            foreach ($magentoOrder->getAllItems() as $orderItem) {
                if (in_array($orderItem->getProductType(), array ('configurable'))) {
                    $validOrderItemIds[] = $orderItem->getItemId();
                }
            }

            foreach ($magentoOrder->getAllItems() as $orderItem) {
                if (!in_array($orderItem->getItemId(), $validOrderItemIds)) {
                    $orderItem->delete();
                }
            }

            if (isset ($netsuiteOrder->paymentMethod)) {
                $magentoPayment = $magentoOrder->getPayment();
                $newPaymentCodeAndCard = $this->getMagentoPaymentMethodCodeAndCard($netsuiteOrder->paymentMethod);
                $magentoPayment->setMethod($newPaymentCodeAndCard->getCode());
                $magentoPayment->setCcType($newPaymentCodeAndCard->getCc());
                $magentoPayment->save();
            }

            $magentoOrder->setSubtotal($_netsuiteSubTotal);
            $magentoOrder->setBaseSubtotal($_netsuiteSubTotal);
            $magentoOrder->setGrandTotal($netsuiteOrder->total);
            $magentoOrder->setBaseSubtotal($netsuiteOrder->total);
            $magentoOrder->setTaxAmount($netsuiteOrder->taxTotal);
            $magentoOrder->setBaseTaxAmount($netsuiteOrder->taxTotal);
        }

        return $magentoOrder;
    }

    protected function getMagentoPaymentMethodCodeAndCard(RecordRef $netsuitePaymentMethod) {
        $paymentMethodCodeAndCard = new Varien_Object();
        $paymentMethodsMap = unserialize(Mage::getStoreConfig('rocketweb_netsuite/payment_methods/netsuite_mapping'));
        foreach($paymentMethodsMap as $paymentMethodsMapItem) {
            if($paymentMethodsMapItem['internal_netsuite_id'] == $netsuitePaymentMethod->internalId) {
                $paymentMethodCodeAndCard->setCode($paymentMethodsMapItem['payment_method']);
                $paymentMethodCodeAndCard->setCc($paymentMethodsMapItem['payment_cc']);
                return $paymentMethodCodeAndCard;
            }
        }
        throw new Exception("Net Suite Payment method with internal id {$netsuitePaymentMethod->internalId} is not mapped to any payment method in Magento");
    }


    protected function getTermsId() {
        return Mage::getStoreConfig('rocketweb_netsuite/orders/terms_id');
    }
    protected function getClassId() {
        return Mage::getStoreConfig('rocketweb_netsuite/orders/class_id');
    }
    protected function getDepartmentId() {
        return Mage::getStoreConfig('rocketweb_netsuite/orders/department_id');
    }
    protected function getSalesRepId() {
        return Mage::getStoreConfig('rocketweb_netsuite/orders/sales_rep_id');
    }
    protected function getLocationId() {
        return Mage::getStoreConfig('rocketweb_netsuite/orders/location_id');
    }

    protected function getCustomFieldList() {
        return unserialize(Mage::getStoreConfig('rocketweb_netsuite/orders/custom_fields_mapping'));
    }
    
    protected function getCanSetTranId() {
        return Mage::getStoreConfig('rocketweb_netsuite/orders/set_tran_id');
    }
    
    protected function getOrderStatusMap($magentoOrderState) {
        $result = null;
        $orderStatusMaps = unserialize(Mage::getStoreConfig('rocketweb_netsuite/orders/status_map'));
        
        foreach ($orderStatusMaps as $orderStatusMap) {
            if ($orderStatusMap['magento_status'] == $magentoOrderState) {
                $result = $orderStatusMap['netsuite_status'];
                break;
            }
        }
        
        return $result;
    }

    protected function getPaymentProcessor(Mage_Sales_Model_Order $magentoOrder) {
        $paymentProcessorConfigItem = $this->getPaymentProcessorConfigItem($magentoOrder);
        if(!is_null($paymentProcessorConfigItem)) {
            $paymentProcessor = new CustomRecordRef();
            $paymentProcessor->internalId = $paymentProcessorConfigItem['internal_netsuite_id'];
            return $paymentProcessor;
        }
        else {
            return null;
        }
    }

    protected function getPaymentProcessorHelper(Mage_Sales_Model_Order $magentoOrder) {
        $paymentProcessorConfigItem = $this->getPaymentProcessorConfigItem($magentoOrder);
        if(!is_null($paymentProcessorConfigItem)) {
            $helper = Mage::helper('rocketweb_netsuite/paymentprocessors_'.$paymentProcessorConfigItem['payment_processor_helper_class']);
            return $helper;
        }
        else {
            return null;
        }
    }

    protected function getPaymentProcessorConfigItem(Mage_Sales_Model_Order $magentoOrder) {
        $paymentProcessorConfig = unserialize(Mage::getStoreConfig('rocketweb_netsuite/payment_methods/processor_mapping'));
        if(is_array($paymentProcessorConfig) && count($paymentProcessorConfig)) {
            foreach($paymentProcessorConfig as $paymentProcessorConfigItem) {
                if($paymentProcessorConfigItem['payment_method'] == $magentoOrder->getPayment()->getMethod()) {
                    return $paymentProcessorConfigItem;
                }
            }
        }
        return null;
    }

    protected function getDiscountItemInternalNetsuiteId() {
        return Mage::getStoreConfig('rocketweb_netsuite/orders/discount_item_id');
    }

    protected function getNotTaxableInternalNetsuiteId() {
        return Mage::getStoreConfig('rocketweb_netsuite/tax_rates/not_taxable_internal_netsuite_id');
    }

    protected function isTaxAppliedAfterDiscount() {
        return Mage::getModel('tax/config')->applyTaxAfterDiscount();
    }

    protected function getStatusForState($magentoState) {
        $collection = Mage::getResourceModel('sales/order_status_collection')->addStateFilter($magentoState)->addFieldToFilter('is_default',1);
        return $collection->getFirstItem();
    }

    protected function getProductDefaultInternalId() {
        return Mage::getStoreConfig('rocketweb_netsuite/exports/product_default_id');
    }
    
    protected function getOrderHistorical() {
        return Mage::getStoreConfig('rocketweb_netsuite/exports/order_historical');
    }
    
    protected function getNetsuitePrice($itemCustomField) {
        $result = 0;
        
        foreach ($itemCustomField as $item) {
            if ($item->internalId == 'custcol_pricebeforediscount') {
                $result = $item->value;
                break;
            }
        }
        
        return $result;
    }
}
