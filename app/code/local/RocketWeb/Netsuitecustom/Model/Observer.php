<?php
class RocketWeb_Netsuitecustom_Model_Observer {

    public function setAttributeSetId($observer) {
        $magentoProduct = $observer->getEvent()->getMagentoProduct();
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $observer->getEvent()->getNetsuiteProduct();

        foreach($inventoryItem->customFieldList->customField as $customField) {
            if($customField->internalId == 'custitem_attributeset') {
                $attributeSetName = Mage::helper('rocketweb_netsuitecustom')->getCustomRecord($customField->value->typeId,$customField->value->internalId);
                $attributeSetId = $this->getAttributeSetId($attributeSetName);
                if(is_null($attributeSetId)) {
                    $attributeSetId = $this->createAttributeSet($attributeSetName);
                }
                $magentoProduct->setAttributeSetId($attributeSetId);
            }
        }
    }

    protected function getAttributeSetId($attributeSetName) {
        $attributeSetCollection =  Mage::getModel('eav/entity_attribute_set')->getCollection();
        $attributeSetCollection->addFieldToFilter('entity_type_id',Mage::getModel('catalog/product')->getResource()->getTypeId());
        $attributeSetCollection->addFieldToFilter('attribute_set_name',$attributeSetName);
        if($attributeSetCollection->getSize()) {
            return $attributeSetCollection->getFirstItem()->getAttributeSetId();
        }
        else {
            return null;
        }
    }

    protected function createAttributeSet($attributeSetName) {
        $attributeSet = Mage::getModel('eav/entity_attribute_set');
        $attributeSet->setEntityTypeId(Mage::getModel('catalog/product')->getResource()->getTypeId());
        $attributeSet->setAttributeSetName($attributeSetName);
        $attributeSet->validate();
        $attributeSet->save();

        $modelGroup = Mage::getModel('eav/entity_attribute_group');
        $modelGroup->setAttributeGroupName('Default');
        $modelGroup->setAttributeSetId($attributeSet->getId());
        $attributeSet->setGroups(array($modelGroup));

        return $attributeSet->getId();
    }

    public function setBrand($observer) {
        $magentoProduct = $observer->getEvent()->getMagentoProduct();
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $observer->getEvent()->getNetsuiteProduct();

        foreach($inventoryItem->customFieldList->customField as $customField) {
            if($customField->internalId == 'custitem_brand') {
                $brandName = Mage::helper('rocketweb_netsuitecustom')->getCustomRecord($customField->value->typeId,$customField->value->internalId);
                $brandId = $this->attributeValueExists('brand',$brandName);
                if($brandId) {
                    $magentoProduct->setBrand($brandId);
                }
                else {
                    $brandId = $this->addAttributeValue('brand',$brandName);
                    $magentoProduct->setBrand($brandId);
                }
            }
        }
    }

    protected function attributeValueExists($arg_attribute, $arg_value)
    {
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;

        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);

        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);

        foreach($options as $option)
        {
            if ($option['label'] == $arg_value)
            {
                return $option['value'];
            }
        }

        return false;
    }

    protected function addAttributeValue($arg_attribute, $arg_value)
    {
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;

        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);

        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);

        if(!$this->attributeValueExists($arg_attribute, $arg_value))
        {
            $value['option'] = array($arg_value,$arg_value);
            $result = array('value' => $value);
            $attribute->setData('option',$result);
            $attribute->save();
        }

        foreach($options as $option)
        {
            if ($option['label'] == $arg_value)
            {
                return $option['value'];
            }
        }
        return true;
    }

    protected function getAttributeValue($arg_attribute, $arg_option_id)
    {
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_table        = Mage::getModel('eav/entity_attribute_source_table');

        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);

        $attribute_table->setAttribute($attribute);

        $option                 = $attribute_table->getOptionText($arg_option_id);

        return $option;
    }

    public function setAndImportCategories($observer) {
        $magentoProduct = $observer->getEvent()->getMagentoProduct();
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $observer->getEvent()->getNetsuiteProduct();



        $masterCategoryId = $this->_getMasterCategory($inventoryItem);
        if($masterCategoryId) {
            $mainCategoryId = $this->_getMainCategoryId($inventoryItem,$masterCategoryId);
            if($mainCategoryId) {
                $subCategoryId = $this->_getSubcategoryId($inventoryItem,$mainCategoryId);
                if($subCategoryId) {
                    $currentCategoryAssociations = is_array($magentoProduct->getCategoryIds())?$magentoProduct->getCategoryIds():array();
                    $currentCategoryAssociations[]=$masterCategoryId;
                    $currentCategoryAssociations[]=$mainCategoryId;
                    $currentCategoryAssociations[]=$subCategoryId;
                    $currentCategoryAssociations = array_unique($currentCategoryAssociations);
                    if(count($currentCategoryAssociations)) {
                        $magentoProduct->setCategoryIds($currentCategoryAssociations);
                    }
                }
            }
        }
    }

    public function initCustomRecords($observer) {
        //Cache custom record types that we need. This is because I cannot do a search inside another search
        Mage::helper('rocketweb_netsuitecustom')->getCustomRecord(36,1);
        Mage::helper('rocketweb_netsuitecustom')->getCustomRecord(31,1);
    }

    protected function _getMasterCategory($inventoryItem) {
        foreach($inventoryItem->customFieldList->customField as $customField) {
            if($customField->internalId == 'custitem_master_category') {
                $categoryName = Mage::helper('rocketweb_netsuite')->getListValue($customField->value->typeId,$customField->value->internalId);
                if($categoryName) {
                    $categoryId = $this->_getCategoryId($categoryName,Mage::getStoreConfig('rocketweb_netsuite/custom/imported_root_category_id'));
                    if(!$categoryId) {
                        $categoryId = $this->_createCategory($categoryName,Mage::getStoreConfig('rocketweb_netsuite/custom/imported_root_category_id'));
                    }
                    return $categoryId;
                }
            }
        }
        return null;
    }

    protected function _getMainCategoryId($inventoryItem,$parentId) {
        foreach($inventoryItem->customFieldList->customField as $customField) {
            if($customField->internalId == 'custitem_category') {
                $categoryName = Mage::helper('rocketweb_netsuitecustom')->getCustomRecord($customField->value->typeId,$customField->value->internalId);
                if($categoryName) {
                    $categoryId = $this->_getCategoryId($categoryName,$parentId);
                    if(!$categoryId) {
                        $categoryId = $this->_createCategory($categoryName,$parentId);
                    }
                    return $categoryId;
                }
            }
        }


        return null;
    }

    protected function _getSubcategoryId($inventoryItem,$parentId) {
        foreach($inventoryItem->customFieldList->customField as $customField) {
            if($customField->internalId == 'custitem_subcategory') {
                $categoryName = Mage::helper('rocketweb_netsuitecustom')->getCustomRecord($customField->value->typeId,$customField->value->internalId);
                if($categoryName) {
                    $categoryId = $this->_getCategoryId($categoryName,$parentId);
                    if(!$categoryId) {
                        $categoryId = $this->_createCategory($categoryName,$parentId);
                    }
                    return $categoryId;
                }
            }
        }
        return null;
    }

    protected function _getCategoryId($categoryName,$parentId) {
        $categories = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('parent_id', array('eq'=>$parentId))->addAttributeToFilter('name',$categoryName);
        if($categories->getSize()) {
            return $categories->getFirstItem()->getId();
        }
        else {
            return null;
        }

    }

    protected function _createCategory($name, $parentId) {
        $category = Mage::getModel('catalog/category');
        $category->setName($name);
        $category->setIsActive(1);
        $category->setIsAnchor(1);
        $parentCategory = $this->_getCategoryById($parentId);
        $category->setPath($parentCategory->getPath());

        $category->save();
        return $category->getId();
    }

    protected function _getCategoryById($id) {
        static $_categoryCache = array();
        if(!isset($_categoryCache[$id])) {
            $_categoryCache[$id] = Mage::getModel('catalog/category')->load($id);
        }
        return $_categoryCache[$id];

    }


}