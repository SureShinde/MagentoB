<?php
class RocketWeb_Netsuite_Model_Process_Import_Inventoryitem extends RocketWeb_Netsuite_Model_Process_Import_Abstract {

    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_PRODUCTS;
    }

    public function getNetsuiteRequest($recordType,$startDateTime) {
        $now = new DateTime(Mage::helper('rocketweb_netsuite')->getServerTime());

        $searchDateField = new SearchDateField();
        $searchDateField->searchValue = $startDateTime;
        $searchDateField->searchValue2 = $now->format(DateTime::ISO8601);
        $searchDateField->operator = SearchDateFieldOperator::within;

        $typeField = new SearchEnumMultiSelectField();
        $typeField->operator = SearchEnumMultiSelectFieldOperator::anyOf;
        $typeField->searchValue = $recordType;

        $tranSearchBasic = new ItemSearchBasic();
        $tranSearchBasic->lastModifiedDate = $searchDateField;
        $tranSearchBasic->type = $typeField;

        Mage::dispatchEvent('netsuite_import_request_before', array('record_type'=>$this->getRecordType(),'search_object'=>$tranSearchBasic));

        $searchRequest = new SearchRequest();
        $searchRequest->searchRecord = $tranSearchBasic;

        return $searchRequest;
    }

    public function getRecordType() {
        return RecordType::inventoryItem;
    }

    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::PRODUCT_UPDATED;
    }

    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::PRODUCT_DELETED;
    }

    public function isAlreadyImported(Record $record) {
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->addAttributeToFilter('netsuite_internal_id',$record->internalId);
        $netsuiteUpdateDatetime = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->lastModifiedDate);

        $productCollection->addAttributeToFilter('netsuite_last_import_date',array('gteq'=>$netsuiteUpdateDatetime));
        $productCollection->load();
        if($productCollection->count()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function process(Record $inventoryItem) {
        /** @var InventoryItem $inventoryItem */
        $magentoProduct = Mage::helper('rocketweb_netsuite/mapper_product')->getMagentoFormatFromInventoryItem($inventoryItem);

        if(!$this->isMagentoImportable($inventoryItem)) {
            if($magentoProduct && $magentoProduct->getId()) {
                //deactivate a product that is not importable anymore
                $magentoProduct->setNetsuiteLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($inventoryItem->lastModifiedDate));
                $magentoProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);

                try {
                    $magentoProduct->save();
                }
                catch(Mage_Eav_Model_Entity_Attribute_Exception $ex) {
                    if(strpos($ex->getMessage(),'url_key attribute already exists')!==false) {
                        $magentoProduct->setUrlKey($this->getNextAvailableUrlKey($magentoProduct->getUrlKey()));
                        $magentoProduct->save();
                    }
                }

                if(Mage::helper('rocketweb_netsuite/changelog')->isChangeLogEnabled()) {
                    Mage::helper('rocketweb_netsuite/changelog')->logChange(RocketWeb_Netsuite_Model_Changelog::PRODUCT_DISABLED,$magentoProduct->getSku(),"");
                }
            }
            return;
        }

        if (is_array($errors = $magentoProduct->validate())) {
            $strErrors = array();
            foreach($errors as $code=>$error) {
                $strErrors[] = ($error === true)? Mage::helper('catalog')->__('Attribute "%s" is invalid.', $code) : $error;
            }
            throw new Exception(implode("\n", $strErrors));
        }

        $magentoProduct->setNetsuiteInternalId($inventoryItem->internalId);
        $magentoProduct->setNetsuiteLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($inventoryItem->lastModifiedDate));

        $productIsNew = false;
        if(!$magentoProduct->getId()) {
            $productIsNew = true;
            try {
                $magentoProduct->save();
            }
            catch(Mage_Eav_Model_Entity_Attribute_Exception $ex) {
                if(strpos($ex->getMessage(),'url_key attribute already exists')!==false) {
                    $magentoProduct->setUrlKey($this->getNextAvailableUrlKey($magentoProduct->getUrlKey()));
                    $magentoProduct->save();
                }
            }
            $magentoProduct = Mage::getModel('catalog/product')->load($magentoProduct->getId());
        }

        Mage::dispatchEvent('netsuite_import_product_created_after',array('magento_product'=>$magentoProduct,'netsuite_product'=>$inventoryItem,'product_is_new'=>$productIsNew));

        if(Mage::getStoreConfig('rocketweb_netsuite/products/sync_product_images')) {
            if($productIsNew || Mage::getStoreConfig('rocketweb_netsuite/products/image_sync_on_update')) {
                $this->removeExistingImages($magentoProduct);

                $imageFieldNames = array('storeDisplayThumbnail','storeDisplayImage');
                foreach($imageFieldNames as $imageFieldName) {
                    if(is_object($inventoryItem->{$imageFieldName})) {
                        $imageItem = $this->getImageItem($inventoryItem->{$imageFieldName}->internalId);
                        if(is_object($imageItem)) {
                            $tmpFilename = tempnam(Mage::helper('rocketweb_netsuite')->getImportDir(),'').'.'.Mage::helper('rocketweb_netsuite/transform')->getFileExtensionFromNetSuiteMediaType($imageItem->fileType);
                            if($tmpFilename) {
                                file_put_contents($tmpFilename,$imageItem->content);
                                if($imageFieldName == 'storeDisplayThumbnail') $fileType = array('thumbnail');
                                if($imageFieldName == 'storeDisplayImage') $fileType = array('image','small_image');

                                if(file_exists($tmpFilename) && filesize($tmpFilename)) {
                                    $magentoProduct->addImageToMediaGallery($tmpFilename, $fileType, true,false);
                                }
                            }
                        }
                    }
                }
            }

        }

	try {
        if(!$productIsNew) {
            $dataDiff = Mage::helper('rocketweb_netsuite')->getProductDataDiff($magentoProduct);
        }
		$magentoProduct->save();
	}
	 catch(Mage_Eav_Model_Entity_Attribute_Exception $ex) {
                if(strpos($ex->getMessage(),'url_key attribute already exists')!==false) {
                    $magentoProduct->setUrlKey($this->getNextAvailableUrlKey($magentoProduct->getUrlKey()));
                    $magentoProduct->save();
                }
            }

     if(Mage::helper('rocketweb_netsuite/changelog')->isChangeLogEnabled()) {
        if($productIsNew) {
            Mage::helper('rocketweb_netsuite/changelog')->logChange(RocketWeb_Netsuite_Model_Changelog::PRODUCT_NEW,$magentoProduct->getSku(),"");
        }
        else {
            Mage::helper('rocketweb_netsuite/changelog')->logChange(RocketWeb_Netsuite_Model_Changelog::PRODUCT_CHANGE,$magentoProduct->getSku(),Mage::helper('rocketweb_netsuite/changelog')->createLogCommentFromDiffArray($dataDiff));
        }
     }

    }

    protected function getNextAvailableUrlKey($urlKey) {
        $products = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('url_key',array('like'=>$urlKey.'%'));
        $products->load();
        $index = count($products);
        return $urlKey.'-'.$index;
    }

    protected function removeExistingImages(Mage_Catalog_Model_Product $product) {
        $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
        $items = $mediaApi->items($product->getId());
        foreach($items as $item)
            $mediaApi->remove($product->getId(), $item['file']);
    }

    protected function getImageItem($netsuiteInternalId) {
        $request = new GetRequest();
        $request->baseRef = new RecordRef();
        $request->baseRef->internalId = $netsuiteInternalId;
        $request->baseRef->type = RecordType::file;

        $getResponse = Mage::helper('rocketweb_netsuite')->getNetsuiteService()->get($request);
        if (!$getResponse->readResponse->status->isSuccess) {
            Mage::helper('rocketweb_netsuite')->log((string) print_r($getResponse->readResponse->status->statusDetail,true));
            return null;
        }
        else {
            return $getResponse->readResponse->record;
        }
    }

    public function isActive() {
        return true;
    }

    public function isMagentoImportable(Record $inventoryItem) {
        $isImportable = new Varien_Object();
        $isImportable->setFlag(true);
        Mage::dispatchEvent('netsuite_inventory_item_is_importable', array('inventory_item'=>$inventoryItem,'is_importable'=>$isImportable));

        return $isImportable->getFlag();
    }
}