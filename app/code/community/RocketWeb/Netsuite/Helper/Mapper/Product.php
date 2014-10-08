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

class RocketWeb_Netsuite_Helper_Mapper_Product extends RocketWeb_Netsuite_Helper_Mapper {

    protected $_attributeCache = array();

    /**
     * @param Mage_Catalog_Model_Product $magentoProduct
     * @return InventoryItem
     */
    public function getNetsuiteFormat(Mage_Catalog_Model_Product $magentoProduct) {

        $inventoryItem = new InventoryItem();
        
        $inventoryItem->externalId = $magentoProduct->getId();
        $inventoryItem->originalItemType = ItemType::_inventoryItem;
        $inventoryItem->originalItemSubtype = ItemSubType::_forSale;
        $inventoryItem->salesDescription = substr($magentoProduct->getDescription(),0,900);
        $inventoryItem->manufacturer = $magentoProduct->getAttributeText('manufacturer');
        $inventoryItem->mpn = $magentoProduct->getSku();
        $inventoryItem->cost = $magentoProduct->getCost();
        $inventoryItem->itemId = $magentoProduct->getSku();
        $inventoryItem->displayName = $magentoProduct->getName();
        $inventoryItem->upcCode = $magentoProduct->getSku();

        
        $prices = array();
        $prices[0] = new Price();
        $prices[0]->value = $magentoProduct->getPrice();
        $prices[0]->quantity = 0;
        
        $currency = new Currency();
        $currency->internalId = 1;
        $priceLevel = new PriceLevel();
        $priceLevel->internalId = 1;
        
        $pricing = array();
        $pricing[0] = new Pricing();
        $pricing[0]->currency = $currency;
        $pricing[0]->priceList = $prices;
        $pricing[0]->discount = 0;
        $pricing[0]->priceLevel = $priceLevel;
        
        $pricingMatrix = new PricingMatrix();
        $pricingMatrix->pricing = $pricing;
        $pricingMatrix->replaceAll = true;

        $inventoryItem->pricingMatrix = $pricingMatrix;
        
        
        if($magentoProduct->getStockItem()->getManageStock()) {
            $inventoryItem->quantityAvailable = $magentoProduct->getStockItem()->getQty();
        }
        
        return $inventoryItem;
    }

    /**
     * @param Mage_Catalog_Model_Product $magentoProduct
     * @return bool
     */
    public function productExistsInNetsuite(Mage_Catalog_Model_Product $magentoProduct) {

        $netsuiteService = $this->_getNetsuiteService();
        
        $netsuiteLinkFieldId = Mage::getStoreConfig('rocketweb_netsuite/products/netsuite_link_field');
        $magentoLinkFieldId = Mage::getStoreConfig('rocketweb_netsuite/products/magento_link_field');
        $searchField = new SearchStringField();
        $searchField->operator = "is";
        $searchField->searchValue = ($magentoLinkFieldId == 'id')?$magentoProduct->getId():$magentoProduct->getData($magentoLinkFieldId);
        
        $search = new ItemSearchBasic();
        $search->{$netsuiteLinkFieldId} = $searchField;
        
        $request = new SearchRequest();
        $request->searchRecord = $search;
        
        $searchResponse = $netsuiteService->search($request);
        if($searchResponse->searchResult->totalRecords != 0) {
            return true;
        }
        else {
            return false;
        }
        
    }

    public function getProductDescription(Mage_Sales_Model_Order_Item $item) {
        $description = $item->getName();

        $customOptions = $item->getProductOptions();
        if(is_array($customOptions) && isset($customOptions['options']) && count($customOptions['options'])) {
            $customOptions = $customOptions['options'];
            $description.=' - ';
            foreach($customOptions as $option) {
                $description.=$option['label'].':'.$option['print_value'].', ';
            }
            $description = preg_replace('/, $/','',$description);
        }

        return $description;
    }

    public function getInventoryAdjustmentRequestForNewProduct(Mage_Catalog_Model_Product $magentoProduct,$netsuiteId) {
        //If the product is new and it has stock, do an inventory adjustment
        $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($magentoProduct)->getQty();
        if($qty) {
            $inventoryAdjustment = new InventoryAdjustment();

            $location = new RecordRef();
            $location->type = RecordType::location;
            $location->internalId = Mage::getStoreConfig('rocketweb_netsuite/stock/order_location');

            $account = new RecordRef();
            $account->type = RecordType::account;
            $account->internalId = Mage::getStoreConfig('rocketweb_netsuite/stock/adjustment_account_id');

            $inventoryItem = new InventoryAdjustmentInventory;
            $inventoryItem->item = new RecordRef();
            $inventoryItem->item->type = RecordType::inventoryItem;
            $inventoryItem->item->internalId = $netsuiteId;
            $inventoryItem->location = clone $location;
            $inventoryItem->adjustQtyBy = $qty;

            $inventoryAdjustment->location = $location;
            $inventoryAdjustment->inventoryList = new InventoryAdjustmentInventoryList();
            $inventoryAdjustment->inventoryList->inventory[]=$inventoryItem;
            $inventoryAdjustment->account = $account;
            $inventoryAdjustment->externalId = Mage::helper('rocketweb_netsuite/mapper_inventory')->getInventoryAdjustmentNameForNewProduct($magentoProduct);

            $request = new AddRequest();
            $request->record = $inventoryAdjustment;
            return $request;
        }
        else {
            return null;
        }

    }

    public function getMagentoFormatFromInventoryItem(InventoryItem $inventoryItem) {
        $magentoProduct = Mage::getModel('catalog/product')->loadByAttribute('netsuite_internal_id',$inventoryItem->internalId);
        if(!is_object($magentoProduct) || !$magentoProduct->getId()) {
            $magentoProduct = Mage::getModel('catalog/product');
        }else{
            $magentoProduct = Mage::getModel('catalog/product')->load($magentoProduct->getId());
        }

        $fieldMap = Mage::getModel('rocketweb_netsuite/config')->getConfigVarMapProductColumns('field_map',null,'products');

        $customValues = array();
        foreach($fieldMap as $fieldData) {
            $customValueListKey = RocketWeb_Netsuite_Model_Product_Map_Value::getCustomValueListKey($fieldData['magento']);

            if(isset($customValues[$customValueListKey])) {
                $productMapValue = $customValues[$customValueListKey];
                $productMapValue->addDefaultValue($fieldData['netsuite'],$fieldData['netsuite_field_value']);


            }
            else {
                $productMapValue = new RocketWeb_Netsuite_Model_Product_Map_Value($fieldData['netsuite'],
                    $fieldData['magento'],
                    $fieldData['netsuite_field_type'],
                    $fieldData['netsuite_list_id'],
                    $fieldData['netsuite_field_value'],
                    $fieldData['netsuite_field_search_class_name'],
                    $fieldData['netsuite_field_name_field']);

                $customValues[$customValueListKey] = $productMapValue;
            }

            $productMapValue->extractValue($inventoryItem,$fieldData['netsuite'],$fieldData['netsuite_field_type']);
        }

        $custitem_qtyso_pendingapproval = 0;
        foreach($customValues as $productMapValue) {
            if ( $productMapValue->getNetsuiteFieldId() == 'custitem_qtyso_pendingapproval' )
            {
                $custitem_qtyso_pendingapproval = $this->getValueForMagento($productMapValue);
                continue;
            }
            $valueForMagento = $this->getValueForMagento($productMapValue);
            $magentoProduct->setData($productMapValue->getMagentoFieldId() ,$valueForMagento);
        }

        if(!$magentoProduct->getId()) {
            $magentoProduct = $this->addDefaultFieldsForNewProduct($magentoProduct,$inventoryItem);

            $quantitySet = false;
            if(isset($inventoryItem->locationsList->locations)) {
                foreach($inventoryItem->locationsList->locations as $location) {
                    if($location->location ==  Mage::getStoreConfig('rocketweb_netsuite/stock/order_location')) {
                        $qty = $location->quantityOnHand;
                        if($qty) {
                            $magentoProduct->setStockData(array(
                                'is_in_stock' => 1,
                                'qty' => $qty,
                                'manage_stock' => 1
                            ));
                            $quantitySet = true;
                            break;
                        }
                    }
                }
            }
            if(!$quantitySet) {
                $magentoProduct->setStockData(array(
                    'is_in_stock' => 0,
                    'qty' => 0,
                    'manage_stock' => 1
                ));
            }

        }else{
            $stock_obj = Mage::getModel('cataloginventory/stock_item')->loadByProduct($magentoProduct->getId());
            $stockData = $stock_obj->getData();

            if(isset($inventoryItem->locationsList->locations)) {
                $qty = 0;
                foreach($inventoryItem->locationsList->locations as $location) {
                    
                    switch($location->locationId->internalId)
                    {
                        case 2 :
                            $quantityAvailableWH = $location->quantityAvailable;
                            break;
                        case 5 :
                            $quantityOnOrderFullfilment = $location->quantityOnOrder;
                            $quantityBackOrderedFullfilment = $location->quantityBackOrdered;
                            break;

                    }
                }

                $stockData['is_in_stock'] = 1;
                //$stockData['qty'] = ($quantityOnHandWH + $quantityOnOrderFullfilment) - $quantityBackOrderedFullfilment - $custitem_qtyso_pendingapproval;
                $stockData['qty'] = ($quantityAvailableWH + $quantityOnOrderFullfilment) - $quantityBackOrderedFullfilment;
                $stockData['manage_stock'] = 1;
                $stock_obj->setData($stockData);
                $stock_obj->save();
                

            }

        }

        


        if(!trim($magentoProduct->getSku())) {
            $magentoProduct->setSku($inventoryItem->internalId);
        }



        $magentoProduct->setUrlKey(Mage::getModel('catalog/product_url')->formatUrlKey($magentoProduct->getName()));
        
        //$magentoProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        //add price, including tier prices if any
        if($inventoryItem->pricingMatrix && is_array($inventoryItem->pricingMatrix->pricing)) {
            $consideredPricingLevelId = Mage::getStoreConfig('rocketweb_netsuite/products/price_level_netsuite_id');
            foreach($inventoryItem->pricingMatrix->pricing as $pricingLevel) {
                if($pricingLevel->priceLevel->internalId == $consideredPricingLevelId) {
                    $tierPrices = array();
                    foreach($pricingLevel->priceList->price as $priceItem) {
                        if($priceItem->quantity == 0) {
                            $magentoProduct->setPrice($priceItem->value);
                        }
                        else {
                            $tierPrices[] = array(
                                'website_id'  => Mage::getStoreConfig('rocketweb_netsuite/products/tier_price_website'),
                                'cust_group'  => Mage::getStoreConfig('rocketweb_netsuite/products/tier_price_customer_group'),
                                'price_qty'   => $priceItem->quantity,
                                'price'       => $priceItem->value
                            );
                        }
                    }
                    if(count($tierPrices)) {
                        if($magentoProduct->getTierPriceCount()) {
                            $this->clearProductTiers($magentoProduct->getId());
                        }
                        $magentoProduct->setTierPrice($tierPrices);
                    }
                }
            }
        }

        $productType = $this->getProductType($inventoryItem);
        switch($productType) {
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                $magentoProduct = $this->setConfigurableData($magentoProduct,$inventoryItem);
                break;
        }


        return $magentoProduct;
    }


    protected function getValueForMagento(RocketWeb_Netsuite_Model_Product_Map_Value $productMapValue) {
        $attribute = $this->_getAttributeByCode($productMapValue->getMagentoFieldId());


        if(is_object($attribute) && in_array($attribute->getFrontendInput(),array('select','multiselect'))) {
            $values = $productMapValue->getValues();
            $selectedOptionIds = array();

            foreach($values as $netsuiteValue) {
                $selectedOptionId = $this->_getOptionIdByOptionLabel($productMapValue->getMagentoFieldId(),$netsuiteValue);
                if(is_null($selectedOptionId)) {
                    $this->_addOptionToAttribute($productMapValue->getMagentoFieldId(),$netsuiteValue);
                    $selectedOptionId = $this->_getOptionIdByOptionLabel($productMapValue->getMagentoFieldId(),$netsuiteValue);
                }
                $selectedOptionIds[] = $selectedOptionId;
            }

            return implode(',',array_unique($selectedOptionIds));

        }
        else {
            $values = $productMapValue->getValues();
            if(isset($values[0])) return $values[0];
            else return null;
        }
    }

    protected function clearProductTiers($productId) {
        $dbc = Mage::getSingleton('core/resource')->getConnection('core_write');
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('catalog/product').'_tier_price';
        $dbc->query("DELETE FROM $table WHERE entity_id = {$productId}");
    }

    protected function addDefaultFieldsForNewProduct(Mage_Catalog_Model_Product $product,InventoryItem $inventoryItem) {
        $productType = $this->getProductType($inventoryItem);

        //fields common for all products
        $product->setTypeId($productType);
        $product->setVisibility(Mage::getStoreConfig('rocketweb_netsuite/products/default_visibility'));
        $product->setStatus(Mage::getStoreConfig('rocketweb_netsuite/products/default_status'));
        $product->setTaxClassId(Mage::getStoreConfig('rocketweb_netsuite/products/default_tax_class_id'));
        $product->setWebsiteIDs(explode(',',Mage::getStoreConfig('rocketweb_netsuite/products/default_website_ids')));
        $product->setStoreIDs(explode(',',Mage::getStoreConfig('rocketweb_netsuite/products/default_store_ids')));
        $product->setAttributeSetId(Mage::getStoreConfig('rocketweb_netsuite/products/default_attribute_set_id'));


        return $product;
    }

    protected function getProductType(InventoryItem $inventoryItem) {
        if($inventoryItem->matrixType == ItemMatrixType::_parent) {
            return Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
        }
        if($inventoryItem->matrixType == ItemMatrixType::_child) {
            return Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
        }

        return Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
    }

    //This method checks if a mapping is valid:
    //  - "List" Net Suite custom fields must be mapped to Magento select/multiselect attributes
    public function checkValidMapping($magentoFieldName,$netsuiteCustomField) {
        if($netsuiteCustomField->value instanceof ListOrRecordRef) {
            $attribute = $this->_getAttributeByCode($magentoFieldName);
            if(in_array($attribute->getFrontendInput(),array('select','multiselect'))) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }
    }

    protected function _getAttributeByCode($attributeCode) {
        if(!isset($this->_attributeCache[$attributeCode])) {
            $this->_attributeCache[$attributeCode] = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attributeCode);
        }
        return $this->_attributeCache[$attributeCode];
    }

    protected function _getOptionIdByOptionLabel($attributeCode,$label) {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
        //I am not using Mage_Eav_Model_Entity_Attribute_Source_Table::getAllOptions since that will cache the first request
        $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->setAttributeFilter($attribute->getId())
            ->setStoreFilter($attribute->getStoreId())
            ->load()
            ->toOptionArray();

        foreach($options as $option) {
            if($option['label'] == $label) {
                return $option['value'];
            }
        }

        return null;
    }

    protected function _getAllOptionsForAttribute($attributeCode) {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
        $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->setAttributeFilter($attribute->getId())
            ->setStoreFilter($attribute->getStoreId())
            ->load()
            ->toOptionArray();
        return $options;
    }

    protected function _getOptionLabelFromOptionId($attributeCode,$optionId) {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);

        $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->setAttributeFilter($attribute->getId())
            ->setStoreFilter($attribute->getStoreId())
            ->load()
            ->toOptionArray();

        foreach($options as $option) {
            if($option['value'] == $optionId) {
                return $option['label'];
            }
        }

        return null;
    }

    protected function _addOptionToAttribute($attributeCode,$value) {
        $attribute = $this->_getAttributeByCode($attributeCode);

        $option['attribute_id'] = $attribute->getAttributeId();
        $option['value']['co_'.$value][0] = $value;


        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($option);
    }

    /**
     * @param Mage_Catalog_Model_Product $magentoProduct
     * @param InventoryItem $inventoryItem
     * @return mixed
     * @throws Exception
     */
    protected function setConfigurableData(Mage_Catalog_Model_Product $magentoProduct,InventoryItem $inventoryItem) {

        $matrixItems = $this->getAllMatrixItems($inventoryItem->internalId);
        if(!count($matrixItems)) {
            throw new Exception((string) print_r("Inventory item {$inventoryItem->internalId} ({$inventoryItem->itemId}) declared as matrix but has no children"));
        }

        $configurableAttributes = $this->getAttributesFromListIds($matrixItems[0]->matrixOptionList->matrixOption);
        $configurableAttributeIds = array();
        foreach($configurableAttributes as $attribute) {
            $configurableAttributeIds[] = $attribute->getId();
        }

        $associatedSkus = array();
        $associatedSimpleProducts = $this->getProductsMagentoProductsFromNetsuiteInventoryItems($matrixItems, $configurableAttributes);
        foreach($associatedSimpleProducts as $associatedSimpleProduct) {
            $associatedSkus[] = $associatedSimpleProduct->getSku();
        }

        $priceChanges = array(); //TODO!!!!

        Mage::helper('rocketweb_netsuite/catalog_product')->associateProducts($magentoProduct,$associatedSkus,$priceChanges,$configurableAttributeIds);

        return $magentoProduct;


    }




    /**
     * Given a list of inventory items, return a product collection
     * @param $inventoryItems
     * @return Mage_Catalog_Model_Product_Collection
     */
    protected function getProductsMagentoProductsFromNetsuiteInventoryItems($inventoryItems,$additionalAttributes = null) {
        $internalIds = array();
        foreach($inventoryItems as $inventoryItem) {
            $internalIds[]=$inventoryItem->internalId;
        }

        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
        if(!is_null($additionalAttributes)) {
            foreach($additionalAttributes as $additionalAttribute) {
                $productCollection->addAttributeToSelect($additionalAttribute->getAttributeCode());
            }
        }
        $productCollection->addAttributeToFilter('netsuite_internal_id',array('in'=>$internalIds));

        return $productCollection;
    }

    /**
     * Given a list array, this methods iterates through the product field map and finds the associated attributes
     * @param $listArray
     * @return array
     * @throws Exception
     */
    protected function getAttributesFromListIds($listArray){
        $fieldMap = Mage::getModel('rocketweb_netsuite/config')->getConfigVarMapProductColumns('field_map',null,'products');
        $attributes = array();

        foreach($listArray as $listArrayItem) {
            $found = false;
            foreach($fieldMap as $fieldMapItem) {
                if($fieldMapItem['netsuite_field_type']!='standard' && $fieldMapItem['netsuite'] == $listArrayItem->internalId) {
                    $found = true;
                    $attribute = $this->_getAttributeByCode($fieldMapItem['magento']);
                    $attributes[]=$attribute;
                    break;
                }
            }

            if(!$found) {
                throw new Exception("No mapping found for {$listArrayItem->internalId}");
            }
        }

        return $attributes;
    }

    /**
     * @param $internalId
     * @return mixed
     * @throws Exception
     */
    public function getAllMatrixItems($internalId) {
        $netsuiteService =  Mage::helper('rocketweb_netsuite')->getNetsuiteService();
        $netsuiteService->setSearchPreferences(false,500);

        $parentField = new SearchMultiSelectField();
        $parentField->operator = SearchMultiSelectFieldOperator::anyOf;
        $parentField->searchValue = new RecordRef();
        $parentField->searchValue->internalId = $internalId;

        $tranSearchBasic = new ItemSearchBasic();
        $tranSearchBasic->parent = $parentField;

        $searchRequest = new SearchRequest();
        $searchRequest->searchRecord = $tranSearchBasic;
        $response = $netsuiteService->search($searchRequest);
        if($response->searchResult->status->isSuccess) {
            return $response->searchResult->recordList->record;
        }
        else {
            throw new Exception((string) print_r($response->searchResult->status->statusDetail,true));
        }
    }


}