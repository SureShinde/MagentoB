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

    public function createPostParams(Mage_Sales_Model_Order $magentoOrder) {
        // set customer
        $netsuiteCustomerId = Mage::helper('rocketweb_netsuite/mapper_customer')->createNetsuiteCustomerFromOrder($magentoOrder);

        if (!$netsuiteCustomerId) {
            throw new Exception("Could not find / create the netsuite customer externalIdString=". $magentoOrder->getCustomerId());
        }

        $vars = array();
        $items = array();

        $vars['tranid'] = $magentoOrder->getIncrementId();
        $vars['trandate'] = date("j/n/Y", strtotime($magentoOrder->getCreatedAt()));
        $vars['custbody_bln_customtrancustomer'] = $netsuiteCustomerId;
        $vars['custbody_customeremail'] = $magentoOrder->getCustomerEmail();
        $vars['subsidiary'] = 2;
        $vars['custbody_bln_customtranshippingcost'] = $magentoOrder->getShippingAmount();
        $vars['custbody_magento_order_id'] = $magentoOrder->getIncrementId();
        $vars['custbody_bln_customtransubtotal'] = $magentoOrder->getSubtotal();
        $vars['custbody_is_order_wholesale'] = $magentoOrder->getIsWholesale();

        $paymentMethodNetsuiteId = Mage::helper('rocketweb_netsuite')->getNetsuitePaymentMethodInternalId($magentoOrder->getPayment());
        if(!is_null($paymentMethodNetsuiteId)) {
            $vars['custbody_paymentmethod'] = $paymentMethodNetsuiteId;
        }

        //shipping method
        $netsuiteShippingInternalId = Mage::helper('rocketweb_netsuite')->getNetsuiteShippingMethodInternalId($magentoOrder->getShippingMethod());
        if(!is_null($netsuiteShippingInternalId)) {
            $vars['custbody_bln_customtran_shipmethod'] = $netsuiteShippingInternalId;
        }

        $locationId = $this->getLocationId();
        if($locationId) {
            $vars['location'] = $locationId;
        }

        $vars['custbody_deliverytype'] = preg_replace('/^(\w+) - /', '', $magentoOrder->getShippingDescription());

        $groupname = Mage::getModel('customer/group')->load($magentoOrder->getCustomerGroupId())->getCustomerGroupCode();
        $vars['custbody_customergroup'] = preg_replace('/^(\w+) - /', '', $groupname);
        $vars['custbody_bln_shipping_phone'] = Mage::helper('rocketweb_netsuite/mapper_address')->getShippingPhoneNumber($magentoOrder->getShippingAddress());
        $vars['shipphone'] = Mage::helper('rocketweb_netsuite/mapper_address')->getShippingPhoneNumber($magentoOrder->getShippingAddress());

        $vars['billing_attr'] = Mage::helper('rocketweb_netsuite/mapper_address')->formatBillingAddress($magentoOrder->getBillingAddress());
        $vars['shipping_attr'] = Mage::helper('rocketweb_netsuite/mapper_address')->formatShippingAddress($magentoOrder->getShippingAddress());

        $confProduct = array();
        $bundProduct = array();

        $fixedPriceBundles = array();

        //taxes
        $taxItem = null;
        $taxInfo = $magentoOrder->getFullTaxInfo();
        if(is_array($taxInfo) && isset($taxInfo[0])) {
            $rate = array_pop($taxInfo[0]['rates']);
            $taxRate = Mage::getModel('tax/calculation_rate')->getCollection()->addFieldToFilter('code',$rate['code'])->getFirstItem();
            $taxNetsuiteId = $taxRate->getNetsuiteInternalId();
        }

        foreach ($magentoOrder->getAllItems() as $item) {
            if ($item->getProductType() == 'configurable') {
                $confProduct[$item->getData('item_id')] = $item->getData('price');
                $confProductDiscount[$item->getData('item_id')] = $item->getData('discount_amount');
                $listProductOnOrder[$item->getData('item_id')]['name'] = $item->getData('name');
            }elseif ($item->getProductType() == 'bundle') {
                $parentItemId = $item->getData('parent_item_id');
                if ($parentItemId == '' || $parentItemId == 0 || empty ($parentItemId)) {
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
                $bundProduct[$item->getData('item_id')][$parentItemId]['qty'] = $item->getData('qty_ordered');
                $bundProduct[$item->getData('item_id')]['totalQtyBundle'] = $item->getData('qty_ordered');
                $bundProduct[$item->getData('item_id')]['priceBundle'] = $item->getData('price') - $totalPriceItemBundle;
                $bundProduct[$item->getData('item_id')]['priceBundle2'] = $item->getData('price');
                $bundProduct[$item->getData('item_id')]['totalPriceItem'] = $totalPriceItemBundle;
                $bundProduct[$item->getData('item_id')]['discountAmount'] = $item->getData('discount_amount');
                $bundProduct[$item->getData('item_id')]['productOptions']   = $item->getData('product_options');
                $listProductOnOrder[$item->getData('item_id')]['name'] = $item->getData('name');
            }
            elseif(isset($bundProduct[$item->getData('parent_item_id')])){
                $bundProduct[$item->getData('parent_item_id')]['totalQty'] += $item->getData('qty_ordered');
                $bundProduct[$item->getData('parent_item_id')][$item->getData('item_id')]['itemQty'] = $item->getData('qty_ordered');
            }
        }

        $line_no = 1;

        foreach ($magentoOrder->getAllItems() as $item) {
            $set_parent_name = '';

            if (in_array($item->getProductType(), array ('configurable', 'bundle'))) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            
            if (!((float) $item->getRowTotal()) && $item->getParentItemId()) {
                if (isset($bundProduct[$item->getData('parent_item_id')]))
                {
                    $price = $item->getRowTotal();
                    $taxPercent = $item->getTaxPercent();
                }
                else
                if (isset($confProduct[$item->getData('parent_item_id')]))
                {
                    $parentItem = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                    $price = $parentItem->getRowTotal();
                    $taxPercent = $parentItem->getTaxPercent();
                }
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

            $set_price = $price;
            
            //$netsuiteOrderItem->amount = $price;
            
            if (!is_null($taxItem)) {
                $itemTaxCode = $taxNetsuiteId;
            }
            else {
                $itemTaxCode = $this->getNotTaxableInternalNetsuiteId();
            }

            //custom fields item
            if (Mage::helper('core')->isModuleEnabled('AW_Points')) {
                $pointsTransaction = Mage::getModel('points/transaction')->loadByOrder($magentoOrder);
            }
            
            $priceBeforeDiscount = 0;
            
            //if (is_array($customFieldsConfig) && count($customFieldsConfig)) {
                $customFields = array ();
                $totalDiscount = 0;
                $totalDiscountOnly = 0; // discount only (without adding Bilna Credit)
                $productOptions = unserialize($item->getData('product_options'));
                
                if($item->getProductType() == 'simple' && $item->getData('price') == 0 || $item->getData('parent_item_id') != ''){
                    if(isset($productOptions['bundle_selection_attributes'])) {
                        $bundleSelectionAttributes = unserialize($productOptions['bundle_selection_attributes']);
                        $priceItemOnBundle = $bundleSelectionAttributes['price'] / $bundleSelectionAttributes['qty'];
                        //$bundlePrice = $bundProduct[$item->getData('parent_item_id')][''] 
                        //$priceItemOnBundle = $bundProduct[$item->getData('parent_item_id')][$item->getData('item_id')]['price'];
                        $pricePerBundle = $bundProduct[$item->getData('parent_item_id')]['priceBundle'] * $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                        //$pricePerBundle = $bundProduct[$item->getData('parent_item_id')]['priceBundle'];
                        //$pricePerBundle = $bundProduct[$item->getData('parent_item_id')][$item->getData('parent_item_id')]['price'] / $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                        $totalQty = $bundProduct[$item->getData('parent_item_id')]['totalQty'];
                        //$finalPriceItem = $priceItemOnBundle + ($pricePerBundle / $totalQty);
                        //Simple@Price + (BundleFixPrice * (Simple@Price/TotalSimplePrice))'
                        
                        //$_simplePrice = $bundProduct[$item->getData('parent_item_id')]['priceBundle2'];
                        $_simplePrice = $bundleSelectionAttributes['price'] / $bundleSelectionAttributes['qty'];
                        $_bundleFixPrice = $bundProduct[$item->getData('parent_item_id')]['priceBundle'];
                        //$_bundleFixPrice = $bundProduct[$item->getData('parent_item_id')]['priceBundle'] * $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                        $_totalSimplePrice = $bundProduct[$item->getData('parent_item_id')]['totalPriceItem'];
                        $finalPriceItem = $_simplePrice + ($_bundleFixPrice * ($_simplePrice / $_totalSimplePrice));
                        //echo "finalPriceItem: " . $finalPriceItem . " = ". $_simplePrice."+ (".$_bundleFixPrice."*(".$_simplePrice."/".$_totalSimplePrice."))\n";
                        //$item->setData('price', $finalPriceItem);
                    }
                    if(!empty($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0) {
                        $finalPriceItem = $confProduct[$item->getData('parent_item_id')];
                    }
                }

                /**/
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
                /**/

                // SET PARENT ITEM ID
                $set_parent_item_id = $item->getData('parent_item_id');

                // SET PARENT NAME
                $productBundleName = $listProductOnOrder[$item->getData('parent_item_id')]['name'];//$item->getData('name');
                if( $item->getData('parent_item_id') != '' && isset($bundProduct[$item->getData('parent_item_id')]) )
                {
                    /*
                    0: separate
                    1: together
                    */
                    $productOptionsBundle = unserialize($bundProduct[$item->getData('parent_item_id')]['productOptions']);
                    if($productOptionsBundle['shipment_type']==0)
                    {
                        $set_parent_name = 'Bundle Product Together - ' . $productBundleName;
                    }elseif($productOptionsBundle['shipment_type']==1){
                        $set_parent_name = 'Bundle Product Separate - ' . $productBundleName;
                    }
                }elseif($item->getData('parent_item_id') != ''){
                    $set_parent_name = 'Configurable Product - ' . $productBundleName;
                }

                // SET DISCOUNT PER ITEM
                $totalDiscount += $discountPerItem;
                $set_discount_amount = $discountPerItem;

                // SET BILNA CREDIT
                $bilna_credit = $pointsTransaction->getData('base_points_to_money');
                $subTotal = $magentoOrder->getSubtotal();
                $orderDiscountAmount = $magentoOrder->getDiscountAmount();
                $bilnaCreditItem = 0;
                if ($item->getProductType() == 'simple' && $item->getData('price') == 0 || $item->getData('parent_item_id') != '') {
                    if (isset ($productOptions['bundle_selection_attributes'])) {
                        //$bilna_credit = $pointsTransaction->getData('base_points_to_money');
                        //$subTotal = $magentoOrder->getSubtotal();
                        //$bilnaCreditItem = $finalPriceItem * ($bilna_credit / $subTotal);
                        $bilnaCreditItem = ( $finalPriceItem + $discountPerItem ) * ($bilna_credit / ($subTotal + $orderDiscountAmount) );
                    }
                    
                    if (!empty ($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0) {
                        //$bilnaCreditItem = $confProduct[$item->getData('parent_item_id')] * ($bilna_credit / $subTotal);
                        $bilnaCreditItem = ($confProduct[$item->getData('parent_item_id')] + $discountPerItem ) * ($bilna_credit / ($subTotal + $orderDiscountAmount) );
                    }
                }else{
                    //$bilna_credit = $pointsTransaction->getData('base_points_to_money');
                    //$subTotal = $magentoOrder->getSubtotal();
                    //$bilnaCreditItem = $item->getData('price') * ($bilna_credit / $subTotal);
                    $bilnaCreditItem = ($item->getData('price') + $discountPerItem) * ($bilna_credit / ($subTotal + $orderDiscountAmount) );
                }

                $set_bilna_credit = $bilnaCreditItem;
                
                $totalDiscountOnly = $totalDiscount;
                $totalDiscount += $bilnaCreditItem;

                // SET PRICE BEFORE DISCOUNT
                if ($item->getProductType() == 'bundle') {
                    $item->setData('price', 0);
                }
                elseif ($item->getProductType() == 'simple' && $item->getData('price') == 0) {
                    if (isset ($productOptions['bundle_selection_attributes'])) {
                        $item->setData('price', $finalPriceItem);
                    }
                    
                    if (!empty ($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0) {
                        $item->setData('price', $confProduct[$item->getData('parent_item_id')]);
                    }
                }

                $priceBeforeDiscount = $this->_getCustomFieldValueFromMagentoData($customFieldsConfigItem, $item);
                $set_price_before_discount = $set_price;
                
            //}
            
            if(!empty($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0 ){
                $priceBeforeDiscount = $confProduct[$item->getData('parent_item_id')];
                $set_price_before_discount = $priceBeforeDiscount;
                $final_unit_price = ($set_price_before_discount - abs($totalDiscount));
            }
            else
            {
                $set_price_before_discount = $set_price_before_discount / $item->getData('qty_ordered');
                $final_unit_price = ($set_price_before_discount - abs($totalDiscount));
            }

            $items[] = array(
                'account' => 1,
                'custcol_bln_customtran_quantity' => $item->getQtyOrdered(),
                'custcol_bln_customtran_item' => ( $product->getNetsuiteInternalId() ? $product->getNetsuiteInternalId() : $this->getProductDefaultInternalId() ),
                'custcol_bln_customtran_skubilna' => $product->getSku(),
                'custcol_magentoitemid' => $item->getId(),
                //'amount' => ($set_price - (abs($totalDiscount) * $item->getData('qty_ordered'))),
                'amount' => round($final_unit_price * $item->getQtyOrdered(), 2),
                'custcol_discountitem' =>  abs($totalDiscountOnly), // discount of 1 item only without bilna credit
                'custcol_so_lineid' => $item->getProductId() . "_" . $line_no,
                'custcol_pricebeforediscount' => $set_price_before_discount,
                'custcol_bilnacredit' => abs($set_bilna_credit), // bilna credit of 1 item
                'custcol_parentid' => $set_parent_item_id,
                'custcol_parentname' => $set_parent_name,
                'custcol_bln_customtran_taxcode' => $itemTaxCode,
                'custcol_bln_customtran_unitprice' => $final_unit_price,
                'custcol_is_item_wholesale' => $item->getIsWholesale()
            );

            $line_no++;
        }

        $vars['items'] = $items;

        return $vars;
    }

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
            if ($item->getProductType() == 'configurable') {
                $confProduct[$item->getData('item_id')] = $item->getData('price');
                $confProductDiscount[$item->getData('item_id')] = $item->getData('discount_amount');
                $listProductOnOrder[$item->getData('item_id')]['name'] = $item->getData('name');
            }elseif ($item->getProductType() == 'bundle') {
                $parentItemId = $item->getData('parent_item_id');
                if ($parentItemId == '' || $parentItemId == 0 || empty ($parentItemId)) {
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
                $bundProduct[$item->getData('item_id')][$parentItemId]['qty'] = $item->getData('qty_ordered');
                $bundProduct[$item->getData('item_id')]['totalQtyBundle'] = $item->getData('qty_ordered');
                $bundProduct[$item->getData('item_id')]['priceBundle'] = $item->getData('price') - $totalPriceItemBundle;
                $bundProduct[$item->getData('item_id')]['priceBundle2'] = $item->getData('price');
                $bundProduct[$item->getData('item_id')]['totalPriceItem'] = $totalPriceItemBundle;
                $bundProduct[$item->getData('item_id')]['discountAmount'] = $item->getData('discount_amount');
                $bundProduct[$item->getData('item_id')]['productOptions']   = $item->getData('product_options');
                $listProductOnOrder[$item->getData('item_id')]['name'] = $item->getData('name');
            }
            elseif(isset($bundProduct[$item->getData('parent_item_id')])){
                $bundProduct[$item->getData('parent_item_id')]['totalQty'] += $item->getData('qty_ordered');
                $bundProduct[$item->getData('parent_item_id')][$item->getData('item_id')]['itemQty'] = $item->getData('qty_ordered');
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
                
                if($item->getProductType() == 'simple' && $item->getData('price') == 0 || $item->getData('parent_item_id') != ''){
                    if(isset($productOptions['bundle_selection_attributes'])) {
                        $bundleSelectionAttributes = unserialize($productOptions['bundle_selection_attributes']);
                        $priceItemOnBundle = $bundleSelectionAttributes['price'] / $bundleSelectionAttributes['qty'];
                        //$bundlePrice = $bundProduct[$item->getData('parent_item_id')][''] 
                        //$priceItemOnBundle = $bundProduct[$item->getData('parent_item_id')][$item->getData('item_id')]['price'];
                        $pricePerBundle = $bundProduct[$item->getData('parent_item_id')]['priceBundle'] * $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                        //$pricePerBundle = $bundProduct[$item->getData('parent_item_id')]['priceBundle'];
                        //$pricePerBundle = $bundProduct[$item->getData('parent_item_id')][$item->getData('parent_item_id')]['price'] / $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                        $totalQty = $bundProduct[$item->getData('parent_item_id')]['totalQty'];
                        //$finalPriceItem = $priceItemOnBundle + ($pricePerBundle / $totalQty);
                        //Simple@Price + (BundleFixPrice * (Simple@Price/TotalSimplePrice))'
                        
                        //$_simplePrice = $bundProduct[$item->getData('parent_item_id')]['priceBundle2'];
                        $_simplePrice = $bundleSelectionAttributes['price'] / $bundleSelectionAttributes['qty'];
                        $_bundleFixPrice = $bundProduct[$item->getData('parent_item_id')]['priceBundle'];
                        //$_bundleFixPrice = $bundProduct[$item->getData('parent_item_id')]['priceBundle'] * $bundProduct[$item->getData('parent_item_id')]['totalQtyBundle'];
                        $_totalSimplePrice = $bundProduct[$item->getData('parent_item_id')]['totalPriceItem'];
                        $finalPriceItem = $_simplePrice + ($_bundleFixPrice * ($_simplePrice / $_totalSimplePrice));
                        //echo "finalPriceItem: " . $finalPriceItem . " = ". $_simplePrice."+ (".$_bundleFixPrice."*(".$_simplePrice."/".$_totalSimplePrice."))\n";
                        //$item->setData('price', $finalPriceItem);
                    }
                    if(!empty($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0) {
                        $finalPriceItem = $confProduct[$item->getData('parent_item_id')];
                    }
                }

                /**/
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
                /**/        

                foreach ($customFieldsConfig as $customFieldsConfigItem) {
                    switch ($customFieldsConfigItem['netsuite_field_name']) {
                        case 'custcol_magentoitemid':
                        case 'custcol_parentid':
                            $customField = $this->_initCustomField($customFieldsConfigItem, $item);
                            $customFields[] = $customField;
                            break;
                        case 'custcol_parentname':
                            $productBundleName = $listProductOnOrder[$item->getData('parent_item_id')]['name'];//$item->getData('name');
                            if( $item->getData('parent_item_id') != '' && isset($bundProduct[$item->getData('parent_item_id')]) )
                            {
                                /*
                                0: separate
                                1: together
                                */
                                $productOptions = unserialize($bundProduct[$item->getData('parent_item_id')]['productOptions']);
                                if($productOptions['shipment_type']==0)
                                {
                                    $item->setData('name', 'Bundle Product Together - ' . $productBundleName);
                                }elseif($productOptions['shipment_type']==1){
                                    $item->setData('name', 'Bundle Product Separate - ' . $productBundleName);
                                }
                                $customField = $this->_initCustomField($customFieldsConfigItem, $item);
                                $customFields[] = $customField;
                            }elseif($item->getData('parent_item_id') != ''){
                                $item->setData('name', 'Configurable Product - ' . $productBundleName);
                                $customField = $this->_initCustomField($customFieldsConfigItem, $item);
                                $customFields[] = $customField;
                            }
                            break;
                        case 'custcol_discountitem':
                            if (in_array($item->getProductType(), array ('bundle'))) {
                                continue;
                            }

                            /*$bundleDiscount = 0;
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
                            }*/

                            $totalDiscount += $discountPerItem;
                            $item->setData('discount_amount', $discountPerItem);
                            $customField = $this->_initCustomField($customFieldsConfigItem, $item);
                            $customFields[] = $customField;
                            break;
                        case 'custcol_bilnacredit':
                            if (in_array($item->getProductType(), array ('bundle'))) {
                                continue;
                            }
                            
                            $bilna_credit = $pointsTransaction->getData('base_points_to_money');
                            $subTotal = $magentoOrder->getSubtotal();
                            $orderDiscountAmount = $magentoOrder->getDiscountAmount();
                            $bilnaCreditItem = 0;
                            if ($item->getProductType() == 'simple' && $item->getData('price') == 0 || $item->getData('parent_item_id') != '') {
                                if (isset ($productOptions['bundle_selection_attributes'])) {
                                    //$bilna_credit = $pointsTransaction->getData('base_points_to_money');
                                    //$subTotal = $magentoOrder->getSubtotal();
                                    //$bilnaCreditItem = $finalPriceItem * ($bilna_credit / $subTotal);
                                    $bilnaCreditItem = ( $finalPriceItem + $discountPerItem ) * ($bilna_credit / ($subTotal + $orderDiscountAmount) );
                                }
                                
                                if (!empty ($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0) {
                                    //$bilnaCreditItem = $confProduct[$item->getData('parent_item_id')] * ($bilna_credit / $subTotal);
                                    $bilnaCreditItem = ($confProduct[$item->getData('parent_item_id')] + $discountPerItem ) * ($bilna_credit / ($subTotal + $orderDiscountAmount) );
                                }
                            }else{
                                //$bilna_credit = $pointsTransaction->getData('base_points_to_money');
                                //$subTotal = $magentoOrder->getSubtotal();
                                //$bilnaCreditItem = $item->getData('price') * ($bilna_credit / $subTotal);
                                $bilnaCreditItem = ($item->getData('price') + $discountPerItem) * ($bilna_credit / ($subTotal + $orderDiscountAmount) );
                            }
                            
                            $pointsTransaction->setData('base_points_to_money', $bilnaCreditItem);
                            $customField = $this->_initCustomField($customFieldsConfigItem, $pointsTransaction);
                            $customFields[] = $customField;
                            $totalDiscount += $bilnaCreditItem;
                            break;
                            
                        case 'custcol_pricebeforediscount':
                            if ($item->getProductType() == 'bundle') {
                                $item->setData('price', 0);
                            }
                            elseif ($item->getProductType() == 'simple' && $item->getData('price') == 0) {
                                if (isset ($productOptions['bundle_selection_attributes'])) {
                                    $item->setData('price', $finalPriceItem);
                                }
                                
                                if (!empty ($confProduct) && $confProduct[$item->getData('parent_item_id')] > 0) {
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
                    elseif ($customFieldsConfigItem['netsuite_field_name'] == 'custbody_deliverytype') {
                        $customPaymentMethod = new StringCustomFieldRef();
                        $customPaymentMethod->internalId = 'custbody_deliverytype';
                        $customPaymentMethod->value = preg_replace('/^(\w+) - /', '', $magentoOrder->getShippingDescription());
                        $customFields[] = $customPaymentMethod;
                    }
                    elseif ($customFieldsConfigItem['netsuite_field_name'] == 'custbody_customergroup') {
                        $groupname = Mage::getModel('customer/group')->load($magentoOrder->getCustomerGroupId())->getCustomerGroupCode();
                        $customerGroup = new StringCustomFieldRef();
                        $customerGroup->internalId = 'custbody_customergroup';
                        $customerGroup->value = preg_replace('/^(\w+) - /', '', $groupname);
                        $customFields[] = $customerGroup;
                    }
                    elseif ($customFieldsConfigItem['netsuite_field_name'] == 'custbody_bln_company_name') {
                        $address = $magentoOrder->getShippingAddress();
                        $companyName = new StringCustomFieldRef();
                        $companyName->internalId = 'custbody_bln_company_name';
                        $companyName->value = $address->getCompany();
                        $customFields[] = $companyName;
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
        $customField->value = $this->_getCustomFieldValueFromMagentoData($customFieldsConfigItem,$magentoOrder);

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
        
        if (!$getResponse->readResponse->status->isSuccess) {
            throw new Exception((string) print_r($getResponse->readResponse->status->statusDetail,true));
        }
        else {
            return $getResponse->readResponse->record;
        }

    }

    public function getMagentoFormat(SalesOrder $netsuiteOrder) {
        $netsuiteOrderId = $netsuiteOrder->internalId;
        if (!is_null($netsuiteOrder->customFieldList->customField))
        {
            foreach ($netsuiteOrder->customFieldList->customField as $customField) {
                if ($customField->internalId == 'custbody_sourcero') {
                    $netsuiteOrderId = $customField->value->internalId;
                    break;
                }
            }
        }
        
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
       
        if (!$magentoOrders->count()) {
            throw new Exception("Order with internal Netsuite id #{$netsuiteOrderId} not found!");
        }
        
        $magentoOrder = $magentoOrders->getFirstItem();
        /* comment out because I can see the following $netsuiteCustomer is not used.
        This is to minimize the call to Netsuite Web Service
        $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getByInternalId($netsuiteOrder->entity->internalId);
        */
        $magentoOrderState = Mage::helper('rocketweb_netsuite/transform')->netsuiteStatusToMagentoOrderState($netsuiteOrder->orderStatus);
        
        /*check order magento status */
        if($magentoOrder->getStatus() != 'canceled' ){
            /**
             * check netsuite order status
             */
            if ($magentoOrderState == 'canceled' || $magentoOrderState == 'closed') {
                if ($magentoOrder->canCancel()) {
                    $magentoOrder->cancel();
                    $magentoOrderHistory = $magentoOrder->addStatusHistoryComment('');
                    $magentoOrderHistory->setIsCustomerNotified(true);
                }
               
                try{
                    $magentoOrder->save();
                    $netsuiteOrderId = $netsuiteOrder->internalId;
                    if (!is_null($netsuiteOrder->customFieldList->customField))
                    {
                        foreach ($netsuiteOrder->customFieldList->customField as $customField) {
                            if ($customField->internalId == 'custbody_sourcero') {
                                $netsuiteOrderId = $customField->value->internalId;
                                break;
                            }
                        }
                    }
                    $orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
                    $order  = $orders->getFirstItem();

                    $this->cancelAndRefundPoint($order);
                }catch(Exception $ex){
                    Mage::helper('rocketweb_netsuite')->log($ex->getMessage());
                }
            }
        }

        return $magentoOrder;
    }

    // function to cancel and refund point based on Order ( this is also called from Request Order which is cancelled )
    public function cancelAndRefundPoint($order)
    {
        $transaction = Mage::getModel('points/transaction')->loadByOrder($order);
        $order->setMoneyForPoints($transaction->getData('points_to_money'));
        $order->setBaseMoneyForPoints($transaction->getData('base_points_to_money'));
        $order->setPointsBalanceChange(abs($transaction->getData('balance_change')));

        if ($order->getCustomerId()) {

            //if (($order->getBaseSubtotalCanceled() - $order->getOrigData('base_subtotal_canceled'))) {
            if ($order->getBaseSubtotalCanceled()) {

                /* refund all points spent on order */
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                if ($customer->getId()) {

                    $helper = Mage::helper('points');
                    if ($order->getPointsBalanceChange()) {

                        $applyAfter = Mage::helper('points/config')
                                        ->getPointsCollectionOrder($order->getStoreId()) == AW_Points_Helper_Config::AFTER_TAX;

                        if ($applyAfter) {
                            $baseSubtotal = $order->getBaseSubtotalInclTax() - abs($order->getBaseDiscountAmount());
                            $subtotalToCancel =
                                    $order->getBaseSubtotalCanceled() +
                                    $order->getBaseTaxCanceled() -
                                    $order->getBaseDiscountCanceled();

                        } else {
                            $subtotalToCancel =
                                    $order->getBaseSubtotalCanceled() -
                                    $order->getBaseDiscountCanceled();
                            $baseSubtotal = $order->getBaseSubtotal() - abs($order->getBaseDiscountAmount());
                        }

                        $pointsToCancel = floor($order->getPointsBalanceChange() * $subtotalToCancel / $baseSubtotal);
                        if (Mage::helper('points/config')->isRefundPoints($order->getStoreId())) {
                            $comment = $helper->__('Cancelation of order #%s', $order->getIncrementId());
                            $data = array('memo' => new Varien_Object(), 'order' => $order, 'customer' => $customer);
                            $this->_refundSpentPoints($data, new Varien_Object(
                                            array('comment' => $comment, 'points_to_return' => $pointsToCancel)));
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Refund earned points
     * param array $data
     * public orderRefund() --> _refundEarnedPoints()
     */
    protected function _refundSpentPoints($data, $infoObject = null)
    {
        $memo = $data['memo'];
        $order = $data['order'];
        $customer = $data['customer'];
        $helper = Mage::helper('points');

        if (!$order->getPointsBalanceChange()) {
            return;
        }

        if ($infoObject instanceof Varien_Object) {
            $pointsToReturn = $infoObject->getPointsToReturn();
        } else {            
              $pointsToReturn = round((abs($memo->getBaseMoneyForPoints()) / abs($order->getBaseMoneyForPoints())) 
                      * $order->getPointsBalanceChange());
              
            //$pointsToReturn = ($order->getPointsBalanceChange() / $order->getTotalQtyOrdered()) * $memo->getTotalQty(); 
        }
        /* Refund spent points */
        if ($pointsToReturn) {
            if (!$customer->getId()) {
                return;
            }

            if ($infoObject instanceof Varien_Object) {
                $comment = $infoObject->getComment();
            } else {
                $comment = $helper->__('Refund of %d item(s) related to order #%s', 
                        $memo->getTotalQty(), $order->getIncrementId());
            }

            try {
                Mage::getModel('points/api')->addTransaction(
                        $pointsToReturn, 
                        'order_refunded', 
                        $customer, 
                        null, 
                        array('comment' => $comment), 
                        array('store_id' => $order->getStoreId(), 'order_id' => $order->getId(),
                        'balance_change_type' => AW_Points_Helper_Config::MONEY_SPENT
                        )
                );
            } catch (Exception $e) {
                Mage::helper('rocketweb_netsuite')->log($ex->getMessage());
            }
        }
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

    public function findNetsuiteRequestOrder($order_id)
    {
        // for header purposes
        $export_config_same = (int) Mage::getStoreConfig('rocketweb_netsuite/connection_export/same');
        $nlauth_account = Mage::getStoreConfig('rocketweb_netsuite/general/account_id');
        $ns_host = Mage::getStoreConfig('rocketweb_netsuite/general/host');

        if ( $export_config_same == 1 )
        {
            $nlauth_email = Mage::getStoreConfig('rocketweb_netsuite/general/email');
            $nlauth_signature = Mage::getStoreConfig('rocketweb_netsuite/general/password');
            $nlauth_role = Mage::getStoreConfig('rocketweb_netsuite/general/role_id');
        }
        else
        {
            $nlauth_email = Mage::getStoreConfig('rocketweb_netsuite/connection_export/email');
            $nlauth_signature = Mage::getStoreConfig('rocketweb_netsuite/connection_export/password');
            $nlauth_role = Mage::getStoreConfig('rocketweb_netsuite/connection_export/role_id');
        }

        // check whether the url is sandbox or not
        if (strpos($ns_host, 'sandbox') !== false)
        {
            $ns_rest_host = "https://rest.sandbox.netsuite.com";
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/requestorder/sandbox_find_rst_id');
        }
        else
        {
            $ns_rest_host = "https://rest.netsuite.com";
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/requestorder/production_find_rst_id');
        }

        if (!$scriptId)
            return false;

        // variables to be posted to find request order
        $vars['tranid'] = $order_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($ns_rest_host . "/app/site/hosting/restlet.nl?script=$scriptId&deploy=1"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vars));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = 'Authorization: NLAuth nlauth_account='.$nlauth_account.',nlauth_email='.$nlauth_email.',nlauth_signature='.$nlauth_signature.',nlauth_role='.$nlauth_role;
        //$headers[] = 'Accept-Encoding: gzip, deflate';
        //$headers[] = 'Accept-Language: en-US,en;q=0.5';
        //$headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Content-Type: application/json;';
        $headers[] = 'User-Agent-x: SuiteScript-Call';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);

        curl_close($ch);

        if ($server_output !== false)
        {
            $server_output = json_decode($server_output, true);
            if ($server_output['status'] == 'success')
                return $server_output['result'];
            else
            if ($server_output['status'] == 'error')
                return false;
        }
        else
            return false;
    }

    /* addition by Willy
    - check whether order id in Magento already exists in Netsuite */
    public function findNetsuiteSalesOrder($by_field, $search_string)
    {
        $searchField = new SearchStringField();
        $searchField->operator = SearchStringFieldOperator::is;
        $searchField->searchValue = $search_string;
        $search = new TransactionSearchBasic();
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
}