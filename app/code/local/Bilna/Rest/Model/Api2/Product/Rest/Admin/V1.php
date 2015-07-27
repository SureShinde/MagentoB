<?php
/**
 * Description of Mage_Catalog_Model_Api2_Product_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Product_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Product_Rest {
    /**
     * The greatest decimal value which could be stored. Corresponds to DECIMAL (12,4) SQL type
     */
    const MAX_DECIMAL_VALUE = 99999999.9999;
    
    //- configurable product
    protected $_resPrices = array ();

    /**
     * Add special fields to product get response
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _prepareProductForResponse(Mage_Catalog_Model_Product $product, $return = false) {
        $pricesFilterKeys = array ('price_id', 'all_groups', 'website_price');
        $groupPrice = $product->getData('group_price');
        $product->setData('group_price', $this->_filterOutArrayKeys($groupPrice, $pricesFilterKeys, true));
        $tierPrice = $product->getData('tier_price');
        $product->setData('tier_price', $this->_filterOutArrayKeys($tierPrice, $pricesFilterKeys, true));

        $stockData = $product->getStockItem()->getData();
        $stockDataFilterKeys = array ('item_id', 'product_id', 'stock_id', 'low_stock_date', 'type_id',
            'stock_status_changed_auto', 'stock_status_changed_automatically', 'product_name', 'store_id',
            'product_type_id', 'product_status_changed', 'product_changed_websites',
            'use_config_enable_qty_increments'
        );
        $product->setData('stock_data', $this->_filterOutArrayKeys($stockData, $stockDataFilterKeys));
        $product->setData('product_type_name', $product->getTypeId());
        
        if ($return) {
            return $product;
        }
    }
    
    /**
     * Retrieve product data
     *
     * @return array
     */
    protected function _retrieve() {
        $product = $this->_getProduct();
        $this->_prepareProductForResponse($product);
        $result = $this->_retrieveResponse();
        
        return $result;
    }
    
    protected function _retrieveResponse() {
        if (!$this->_product) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $result = array ();
        
        foreach ($this->_product->getData() as $k => $v) {
            $attributeTextArr = array ('brand', 'brands', 'ship_by', 'sold_by');
            $attributeDetailedInfoArr = array ('description', 'additional', 'how_to_use', 'nutrition_fact', 'size_chart', 'more_detail', 'additional_info');

            if (in_array($k, $attributeTextArr)) {
                if ($k == 'brands') {
                    $result[$k] = $this->_getBrandsUrl($v);
                }
                else {
                    $result[$k] = $this->_product->getAttributeText($k);
                }
            }
            elseif (in_array($k, $attributeDetailedInfoArr)) {
                $result['detailed_info'][$k] = $v;
            }
            else {
                $result[$k] = $v;
            }
        }
        
        if ($result) {
            $result['attribute_config'] = $this->_getAttributeConfig();
            $result['review'] = $this->_getProductReview($this->_product->getId());
            $result['images'] = array (
                'default' => $this->_getImageResize($this->_product, $this->_product->getImage()),
                'data' => $this->_getImage(),
            );
        }
        
        return $result;
    }
    
    protected function _getBrandsUrl($brandsId) {
        if (is_null($brandsId) || empty ($brandsId)) {
            return null;
        }
        
        $brands = Mage::getModel('brands/brands')->load($brandsId);
        $route = Mage::getStoreConfig('brands/settings/route', $this->_getStore()->getId());
        
        return array (
            'label' => $brands->getData('title'),
            'url' => sprintf("%s/%s", $route, $brands->getData('url_key'))
        );
    }
    
    protected function _getAttributeConfig() {
        if ($this->_getProduct()->getData('type_id') != 'configurable') {
            return null;
        }
        
        $attributes = array ();
        $options = array ();
        $store = $this->_getStore();
        $taxHelper = Mage::helper('tax');
        $currentProduct = $this->_getProduct();
        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
        
        if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues = array ();
        }
        
        foreach ($this->_getAllowProducts() as $product) {
            $productId  = $product->getId();
            
            foreach ($this->_getAllowAttributes() as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                
                if (!isset ($options[$productAttributeId])) {
                    $options[$productAttributeId] = array ();
                }

                if (!isset ($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array ();
                }
                
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }
        
        $this->_resPrices = array ($this->_preparePrice($currentProduct->getFinalPrice()));
        
        foreach ($this->_getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = array (
               'id' => $productAttribute->getId(),
               'code' => $productAttribute->getAttributeCode(),
               'label' => $attribute->getLabel(),
               'options' => array (),
            );
            
            $optionPrices = array ();
            $prices = $attribute->getPrices();
            
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    
                    $currentProduct->setConfigurablePrice($this->_preparePrice($value['pricing_value'], $value['is_percent']));
                    $currentProduct->setParentId(true);
                    Mage::dispatchEvent('catalog_product_type_configurable_price', array ('product' => $currentProduct));
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset ($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    }
                    else {
                        $productsIndex = array ();
                    }

                    $info['options'][] = array (
                        'id' => $value['value_index'],
                        'label' => $value['label'],
                        'price' => $configurablePrice,
                        'oldPrice' => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
                        'products'  => $productsIndex,
                    );
                    $optionPrices[] = $configurablePrice;
                }
            }
            
            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional - $optionPrice));
                }
            }
            
            if ($this->_validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preconfiguredFlag) {
                $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
                
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }
        
        $config = array (
            'attributes' => $attributes,
            'template' => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice' => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
            'oldPrice' => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId' => $currentProduct->getId(),
            'chooseText' => Mage::helper('catalog')->__('Choose an Option...'),
//            'taxConfig' => $taxConfig
        );
        
        return $config;
    }
    
    /**
     * Get Allowed Products
     *
     * @return array
     */
    protected function _getAllowProducts() {
        $products = array ();
        $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
        $allProducts = $this->_getProduct()->getTypeInstance(true)->getUsedProducts(null, $this->_getProduct());
        
        foreach ($allProducts as $product) {
            //if ($product->isSaleable() || $skipSaleableCheck) {
                $products[] = $product;
            //}
        }
            
        return $products;
    }
    
    /**
     * Get allowed attributes
     *
     * @return array
     */
    protected function _getAllowAttributes() {
        return $this->_getProduct()->getTypeInstance(true)->getConfigurableAttributes($this->_getProduct());
    }
    
    /**
     * Validating of super product option value
     *
     * @param array $attributeId
     * @param array $value
     * @param array $options
     * @return boolean
     */
    protected function _validateAttributeValue($attributeId, &$value, &$options) {
        if (isset ($options[$attributeId][$value['value_index']])) {
            return true;
        }

        return false;
    }
    
    /**
     * Validation of super product option
     *
     * @param array $info
     * @return boolean
     */
    protected function _validateAttributeInfo(&$info) {
        if (count($info['options']) > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculation real price
     *
     * @param float $price
     * @param bool $isPercent
     * @return mixed
     */
    protected function _preparePrice($price, $isPercent = false) {
        if ($isPercent && !empty ($price)) {
            $price = $this->_getProduct()->getFinalPrice() * $price / 100;
        }

        return $this->_registerJsPrice($this->_convertPrice($price, true));
    }
    
    /**
     * Calculation price before special price
     *
     * @param float $price
     * @param bool $isPercent
     * @return mixed
     */
    protected function _prepareOldPrice($price, $isPercent = false) {
        if ($isPercent && !empty ($price)) {
            $price = $this->_getProduct()->getPrice() * $price / 100;
        }

        return $this->_registerJsPrice($this->_convertPrice($price, true));
    }
    
    /**
     * Replace ',' on '.' for js
     *
     * @param float $price
     * @return string
     */
    protected function _registerJsPrice($price) {
        return str_replace(',', '.', $price);
    }
    
    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @param boolean $round
     * @return float
     */
    protected function _convertPrice($price, $round = false) {
        if (empty ($price)) {
            return 0;
        }

        $price = $this->_getStore()->convertPrice($price);
        
        if ($round) {
            $price = $this->_getStore()->roundPrice($price);
        }

        return $price;
    }

    /**
     * Remove specified keys from associative or indexed array
     *
     * @param array $array
     * @param array $keys
     * @param bool $dropOrigKeys if true - return array as indexed array
     * @return array
     */
    protected function _filterOutArrayKeys(array $array, array $keys, $dropOrigKeys = false) {
        $isIndexedArray = is_array(reset($array));
        
        if ($isIndexedArray) {
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $value = array_diff_key($value, array_flip($keys));
                }
            }
            
            if ($dropOrigKeys) {
                $array = array_values($array);
            }
            
            unset ($value);
        }
        else {
            $array = array_diff_key($array, array_flip($keys));
        }

        return $array;
    }

    /**
     * Retrieve list of products
     *
     * @return array
     */
    protected function _retrieveCollection() {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $store = $this->_getStore();
        $collection->setStoreId($store->getId());
        $collection->addAttributeToSelect(array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)));
        $this->_applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        
        $products = $this->_retrieveCollectionResponse($collection->load(), $collection->getSize());
        
        return $products;
    }
    
    protected function _retrieveCollectionResponse($products, $totalRecord) {
        if (!$products) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $result = array ();
        $result['totalRecord'] = $products->getSize();
        
        foreach ($products as $key => $row) {
            $product = $this->_prepareProductForResponse(Mage::getModel('catalog/product')->load($row->getId()), true);
            
            foreach ($product->getData() as $k => $v) {
                $data[$key] = $key;
                $attributeTextArr = array ('brand', 'ship_by', 'sold_by');
                
                if (in_array($k, $attributeTextArr)) {
                    $result[$key][$k] = $row->getAttributeText($k);
                }
                else {
                    $result[$key][$k] = $v;
                }
            }
            
            $result[$key]['review'] = $this->_getProductReview($product->getId());
            $result[$key]['images'] = array (
                'base' => Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()),
                'thumbnail' => $this->_resizeImage($product, $product->getImage(), $this->_imgThumbnail),
                'horizontal' => $this->_resizeImage($product, $product->getImage(), $this->_imgHorizontal),
                'vertical' => $this->_resizeImage($product, $product->getImage(), $this->_imgVertical),
            );
        }
        
        return $result;
    }

    /**
     * Delete product by its ID
     *
     * @throws Mage_Api2_Exception
     */
    protected function _delete() {
        $product = $this->_getProduct();
        
        try {
            $product->delete();
        }
        catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * Create product
     *
     * @param array $data
     * @return string
     */
    protected function _create(array $data) {
        /* @var $validator Mage_Catalog_Model_Api2_Product_Validator_Product */
        $validator = Mage::getModel('catalog/api2_product_validator_product', array (
            'operation' => self::OPERATION_CREATE
        ));

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        $type = $data['type_id'];
        
        if ($type !== 'simple') {
            $this->_critical("Creation of products with type '$type' is not implemented", Mage_Api2_Model_Server::HTTP_METHOD_NOT_ALLOWED);
        }
        
        $set = $data['attribute_set_id'];
        $sku = $data['sku'];

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->setAttributeSetId($set)
            ->setTypeId($type)
            ->setSku($sku);

        foreach ($product->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $product->setData($mediaAttrCode, 'no_selection');
        }

        $this->_prepareDataForSave($product, $data);
        
        try {
            $product->validate();
            $product->save();
            $this->_multicall($product->getId());
        }
        catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e) {
            $this->_critical(self::RESOURCE_UNKNOWN_ERROR);
        }

        return $this->_getLocation($product);
    }

    /**
     * Update product by its ID
     *
     * @param array $data
     */
    protected function _update(array $data) {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->_getProduct();
        /* @var $validator Mage_Catalog_Model_Api2_Product_Validator_Product */
        $validator = Mage::getModel('catalog/api2_product_validator_product', array (
            'operation' => self::OPERATION_UPDATE,
            'product' => $product
        ));

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
        
        if (isset ($data['sku'])) {
            $product->setSku($data['sku']);
        }
        
        // attribute set and product type cannot be updated
        unset ($data['attribute_set_id']);
        unset ($data['type_id']);
        $this->_prepareDataForSave($product, $data);
        
        try {
            $product->validate();
            $product->save();
        }
        catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e) {
            $this->_critical(self::RESOURCE_UNKNOWN_ERROR);
        }
    }

    /**
     * Determine if stock management is enabled
     *
     * @param array $stockData
     * @return bool
     */
    protected function _isManageStockEnabled($stockData) {
        if (!(isset ($stockData['use_config_manage_stock']) && $stockData['use_config_manage_stock'])) {
            $manageStock = isset ($stockData['manage_stock']) && $stockData['manage_stock'];
        }
        else {
            $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_ITEM . 'manage_stock');
        }
        
        return (bool) $manageStock;
    }

    /**
     * Check if value from config is used
     *
     * @param array $data
     * @param string $field
     * @return bool
     */
    protected function _isConfigValueUsed($data, $field) {
        return isset ($data["use_config_$field"]) && $data["use_config_$field"];
    }

    /**
     * Set additional data before product save
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $productData
     */
    protected function _prepareDataForSave($product, $productData) {
        if (isset ($productData['stock_data'])) {
            if (!$product->isObjectNew() && !isset ($productData['stock_data']['manage_stock'])) {
                $productData['stock_data']['manage_stock'] = $product->getStockItem()->getManageStock();
            }
            
            $this->_filterStockData($productData['stock_data']);
        }
        else {
            $productData['stock_data'] = array (
                'use_config_manage_stock' => 1,
                'use_config_min_sale_qty' => 1,
                'use_config_max_sale_qty' => 1,
            );
        }
        
        $product->setStockData($productData['stock_data']);
        // save gift options
        $this->_filterConfigValueUsed($productData, array('gift_message_available', 'gift_wrapping_available'));
        
        if (isset ($productData['use_config_gift_message_available'])) {
            $product->setData('use_config_gift_message_available', $productData['use_config_gift_message_available']);
            
            if (!$productData['use_config_gift_message_available'] && ($product->getData('gift_message_available') === null)) {
                $product->setData('gift_message_available', (int) Mage::getStoreConfig(Mage_GiftMessage_Helper_Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS, $product->getStoreId()));
            }
        }
        
        if (isset ($productData['use_config_gift_wrapping_available'])) {
            $product->setData('use_config_gift_wrapping_available', $productData['use_config_gift_wrapping_available']);
            
            if (!$productData['use_config_gift_wrapping_available'] && ($product->getData('gift_wrapping_available') === null)) {
                $xmlPathGiftWrappingAvailable = 'sales/gift_options/wrapping_allow_items';
                $product->setData('gift_wrapping_available', (int)Mage::getStoreConfig($xmlPathGiftWrappingAvailable, $product->getStoreId()));
            }
        }

        if (isset ($productData['website_ids']) && is_array($productData['website_ids'])) {
            $product->setWebsiteIds($productData['website_ids']);
        }
        
        // Create Permanent Redirect for old URL key
        if (!$product->isObjectNew() && isset ($productData['url_key']) && isset ($productData['url_key_create_redirect'])) {
            $product->setData('save_rewrites_history', (bool) $productData['url_key_create_redirect']);
        }
        
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            //Unset data if object attribute has no value in current store
            if (Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID !== (int) $product->getStoreId() && !$product->getExistsStoreValueFlag($attribute->getAttributeCode()) && !$attribute->isScopeGlobal()) {
                $product->setData($attribute->getAttributeCode(), false);
            }

            if ($this->_isAllowedAttribute($attribute)) {
                if (isset ($productData[$attribute->getAttributeCode()])) {
                    $product->setData($attribute->getAttributeCode(),$productData[$attribute->getAttributeCode()]);
                }
            }
        }
    }

    /**
     * Filter stock data values
     *
     * @param array $stockData
     */
    protected function _filterStockData(&$stockData) {
        $fieldsWithPossibleDefautlValuesInConfig = array ('manage_stock', 'min_sale_qty', 'max_sale_qty', 'backorders', 'qty_increments', 'notify_stock_qty', 'min_qty', 'enable_qty_increments');
        $this->_filterConfigValueUsed($stockData, $fieldsWithPossibleDefautlValuesInConfig);

        if ($this->_isManageStockEnabled($stockData)) {
            if (isset ($stockData['qty']) && (float)$stockData['qty'] > self::MAX_DECIMAL_VALUE) {
                $stockData['qty'] = self::MAX_DECIMAL_VALUE;
            }
            
            if (isset ($stockData['min_qty']) && (int)$stockData['min_qty'] < 0) {
                $stockData['min_qty'] = 0;
            }
            
            if (!isset ($stockData['is_decimal_divided']) || $stockData['is_qty_decimal'] == 0) {
                $stockData['is_decimal_divided'] = 0;
            }
        }
        else {
            $nonManageStockFields = array ('manage_stock', 'use_config_manage_stock', 'min_sale_qty', 'use_config_min_sale_qty', 'max_sale_qty', 'use_config_max_sale_qty');
            
            foreach ($stockData as $field => $value) {
                if (!in_array($field, $nonManageStockFields)) {
                    unset ($stockData[$field]);
                }
            }
        }
    }

    /**
     * Filter out fields if Use Config Settings option used
     *
     * @param array $data
     * @param string $fields
     */
    protected function _filterConfigValueUsed(&$data, $fields) {
        foreach ($fields as $field) {
            if ($this->_isConfigValueUsed($data, $field)) {
                unset ($data[$field]);
            }
        }
    }

    /**
     * Check if attribute is allowed
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param array $attributes
     * @return boolean
     */
    protected function _isAllowedAttribute($attribute, $attributes = null) {
        $isAllowed = true;
        
        if (is_array($attributes) && !(in_array($attribute->getAttributeCode(), $attributes) || in_array($attribute->getAttributeId(), $attributes))) {
            $isAllowed = false;
        }
        
        return $isAllowed;
    }
    
    protected function _getProductReview($productId) {
        $review = Mage::getModel('review/review_summary')->setStoreId($this->_getStore()->getId())->load($productId);
        $result = array (
            'reviews_count' => $review->getData('reviews_count'),
            'rating_summary' => $review->getData('rating_summary'),
        );
        
        return $result;
    }
    
    protected function _getImageResize($product, $imageFile) {
        return array (
            'base' => $this->_getMediaConfig()->getMediaUrl($imageFile), //- 1400x1400
            'thumbnail' => $this->_resizeImage($product, $imageFile, $this->_imgThumbnail),
            'horizontal' => $this->_resizeImage($product, $imageFile, $this->_imgHorizontal),
            'vertical' => $this->_resizeImage($product, $imageFile, $this->_imgVertical),
            'detail' => $this->_resizeImage($product, $imageFile, $this->_imgDetail),
        );
    }
}
