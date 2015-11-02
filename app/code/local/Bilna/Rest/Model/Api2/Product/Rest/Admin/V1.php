<?php
/**
 * Description of Mage_Catalog_Model_Api2_Product_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Product_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Product_Rest {
    public function __construct() {
        parent::__construct();
        
        $this->_initCache();
        $this->_cacheKey = $this->_getCacheKey();
    }

    /**
     * The greatest decimal value which could be stored. Corresponds to DECIMAL (12,4) SQL type
     */
    const MAX_DECIMAL_VALUE = 99999999.9999;
    
    //- configurable product
    protected $_resPrices = array ();
    
    //- product collection
    protected $_attributeProductCollection = array ('entity_id', 'type_id', 'sku', 'name', 'url_key', 'url_path', 'special_price', 'status', 'visibility', 'price_type', 'price', 'price_view', 'special_from_date', 'special_to_date', 'news_from_date', 'news_to_date', 'group_price', 'tier_price', 'is_in_stock', 'is_salable', 'stock_data', 'attribute_set_id', 'short_description');

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
        $response = $this->_retrieveResponse();
        
        return $response;
    }
    
    protected function _retrieveResponse() {
        if (!$this->_product) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $attributeTextArr = array ('brand', 'brands', 'ship_by', 'sold_by');
        $attributeDetailedInfoArr = array ('description', 'additional', 'how_to_use', 'nutrition_fact', 'size_chart', 'more_detail', 'additional_info');
        $result = array ();
        
        foreach ($this->_product->getData() as $k => $v) {
            if ($this->_stockDataOnly) {
                if ($k == 'stock_data') {
                    $result[$k] = $this->_getStockDataConfig($v);
                }
                
                continue;
            }

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
            elseif ($k == 'stock_data') {
                $result[$k] = $this->_getStockDataConfig($v);
            }
            else {
                $result[$k] = $v;
            }
        }
        
        if ($result) {
            $result['attribute_config'] = $this->_getAttributeConfig();
            $result['attribute_bundle'] = $this->_getAttributeBundle();
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
    
    protected function _getStockDataConfig($stockData) {
        $result = array ();
        $configKey = 'use_config';
        $configRemoveKey = 'use_';
        
        foreach ($stockData as $key => $value) {
            $result[$key] = $value;
            
            if ($this->_isStockDataConfig($configKey, $key)) {
                $result[$this->_getConfigStockDataKey($configRemoveKey, $key)] = $this->_getConfigStockDataValue($configKey, $key);
            }
        }
        
        return $result;
    }
    
    protected function _isStockDataConfig($configKey, $key) {
        if (substr($key, 0, strlen($configKey)) == $configKey) {
            return true;
        }
        
        return false;
    }
    
    protected function _getConfigStockDataKey($configRemoveKey, $key) {
        return substr($key, strlen($configRemoveKey), (strlen($key) - strlen($configRemoveKey)));
    }

    protected function _getConfigStockDataValue($configKey, $key) {
        if ($key == 'use_config_enable_qty_inc') {
            $path = 'enable_qty_increments';
        }
        else {
            $path = substr($key, (strlen($configKey) + 1), (strlen($key) - strlen($configKey)));
        }
                
        return Mage::getStoreConfig('cataloginventory/item_options/' . $path, $this->_getStore());
    }

    protected function _getAttributeConfig($currentProduct = null) {
        if (is_null($currentProduct)) {
            $currentProduct = $this->_getProduct();
        }
        
        if ($currentProduct->getData('type_id') != 'configurable') {
            return null;
        }
        
        $attributes = array ();
        $options = array ();
        $store = $this->_getStore();
        $taxHelper = Mage::helper('tax');
        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
        
        if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues = array ();
        }
        
        foreach ($this->_getAllowProducts($currentProduct) as $product) {
            $productId  = $product->getId();
            
            foreach ($this->_getAllowAttributes($currentProduct) as $attribute) {
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
        
        foreach ($this->_getAllowAttributes($currentProduct) as $attribute) {
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
    protected function _getAllowProducts($_currentProduct) {
        $products = array ();
        $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
        $allProducts = $_currentProduct->getTypeInstance(true)->getUsedProducts(null, $_currentProduct);
        
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
    protected function _getAllowAttributes($_currentProduct) {
        return $_currentProduct->getTypeInstance(true)->getConfigurableAttributes($_currentProduct);
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
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setStore($this->_getStore());
        //$collection->addAttributeToSelect(array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)));
        $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            //->addUrlRewrite($this->_getCategoryId())
            ->addPriceData($this->_getCustomerGroupId());

        $this->_applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        $this->_applyCollectionProductStatus($collection);
        $this->_applyCollectionProductVisibility($collection);

        $response = $this->_retrieveCollectionResponse($collection->load(), $collection->getSize());
        
        return $response;
    }
    
    protected function _retrieveCollectionResponse($products, $totalRecord) {
        if (!$products) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $result = array ();
        $result[0] = array ('total_record' => $products->getSize());
        
        foreach ($products as $key => $row) {
            $product = $this->_prepareProductForResponse($this->_getProduct($row->getId()), true);
            
            foreach ($product->getData() as $k => $v) {
                if (!in_array($k, $this->_attributeProductCollection)) {
                    continue;
                }
                
                if ($this->_stockDataOnly) {
                    if ($k == 'stock_data') {
                        $result[$key][$k] = $this->_getStockDataConfig($v);
                    }
                    
                    continue;
                }
                
                $data[$key] = $key;
                $attributeTextArr = array ('brand', 'ship_by', 'sold_by');
                
                if (in_array($k, $attributeTextArr)) {
                    $result[$key][$k] = $row->getAttributeText($k);
                }
                elseif ($k == 'stock_data') {
                    $result[$key][$k] = $this->_getStockDataConfig($v);
                    //continue;
                }
                else {
                    $result[$key][$k] = $v;
                }
            }
            
            if ($result) {
                $result[$key]['attribute_config'] = $this->_getAttributeConfig($product);
                $result[$key]['attribute_bundle'] = $this->_getAttributeBundle($product);
                $result[$key]['review'] = $this->_getProductReview($product->getId());
                $result[$key]['images'] = array (
                    'base' => Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()),
                    'thumbnail' => $this->_resizeImage($product, $product->getImage(), $this->_imgThumbnail),
                    'horizontal' => $this->_resizeImage($product, $product->getImage(), $this->_imgHorizontal),
                    'vertical' => $this->_resizeImage($product, $product->getImage(), $this->_imgVertical),
                );
            }
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
    
    /**
     * Product Bundle
     * return array
     */
    protected function _getAttributeBundle($product = null) {
        if (is_null($product)) {
            $product = $this->_getProduct();
        }
        
        if ($product->getData('type_id') != 'bundle' || !$product->isSaleable()) {
            return null;
        }
        
        $bundle = array ();
        $bundle['price'] = $this->_getBundlePrice($product);
        $bundle['price_view'] = $this->_getBundlePriceView($product);
        $options = Mage::helper('core')->decorateArray($this->_getBundleOptions($product));
        
        if ($options) {
            $x = 1;
            foreach ($options as $option) {
                $showSingle = $this->_showSingle($option);
                $selections = $option->getSelections();
                       
                $bundle['options'][] = array (
                    'id' => $option->getId(),
                    'title' => $option->getTitle(),
                    'required' => $option->getRequired(),
                    'type' => $option->getType(),
                    'position' => $option->getPosition(),
                    'show_single' => $showSingle,
                    'selection_data' => $this->_getBundleSelectionData($showSingle, $product, $option, $selections),
                );
            }
        }
        
        return $bundle;
    }
    
    protected function _getSelectionCanChangeQty($product, $_option) {
        $_default = $_option->getDefaultSelection();
        $_selections = $_option->getSelections();
        $selectedOptions = $this->_getSelectedOptions($product, $_option);
        $inPreConfigured = $product->hasPreconfiguredValues() && $product->getPreconfiguredValues()->getData('bundle_option_qty/' . $_option->getId());
        
        if (empty ($selectedOptions) && $_default) {
            $_defaultQty = $_default->getSelectionQty() * 1;
            $_canChangeQty = $_default->getSelectionCanChangeQty();
        }
        elseif (!$inPreConfigured && $selectedOptions && is_numeric($selectedOptions)) {
            $selectedSelection = $_option->getSelectionById($selectedOptions);
            $_defaultQty = $selectedSelection->getSelectionQty() * 1;
            $_canChangeQty = $selectedSelection->getSelectionCanChangeQty();
        }
        elseif (!$this->_showSingle() || $inPreConfigured) {
            $_defaultQty = $this->_getSelectedQty($product, $_option);
            $_canChangeQty = (bool) $_defaultQty;
        }
        else {
            $_defaultQty = $_selections[0]->getSelectionQty() * 1;
            $_canChangeQty = $_selections[0]->getSelectionCanChangeQty();
        }

        return array ($_defaultQty, $_canChangeQty);
    }
    
    protected function _getSelectedOptions($product, $option) {
        $result = array ();

        if ($product->hasPreconfiguredValues()) {
            $configValue = $product->getPreconfiguredValues()->getData('bundle_option/' . $option->getId());
            
            if ($configValue) {
                $result = $configValue;
            }
            elseif (!$option->getRequired()) {
                $result = 'None';
            }
        }

        return $result;
    }
    
    protected function _getSelectedQty($product, $option) {
        if ($product->hasPreconfiguredValues()) {
            $selectedQty = (float) $product->getPreconfiguredValues()->getData('bundle_option_qty/' . $option->getId());
            
            if ($selectedQty < 0) {
                $selectedQty = 0;
            }
        }
        else {
            $selectedQty = 0;
        }

        return $selectedQty;
    }

    protected function _getBundlePrice($product) {
        $coreHelper = Mage::helper('core');
        $weeeHelper = Mage::helper('weee');
        $taxHelper = Mage::helper('tax');
        $priceModel = $product->getPriceModel();
        $weeeTaxAmount = 0;
        $_result = array ();
        
        list ($minimalPriceTax, $maximalPriceTax) = $priceModel->getTotalPrices($product, null, null, false);
        list ($minimalPriceInclTax, $maximalPriceInclTax) = $priceModel->getTotalPrices($product, null, true, false);
        
        if ($product->getPriceType() == 1) {
            $weeeTaxAmount = $weeeHelper->getAmount($product);
            $weeeTaxAmountInclTaxes = $weeeTaxAmount;
            
            if ($weeeHelper->isTaxable()) {
                $attributes = $weeeHelper->getProductWeeeAttributesForRenderer($product, null, null, null, true);
                $weeeTaxAmountInclTaxes = $weeeHelper->getAmountInclTaxes($attributes);
            }
            
            if ($weeeTaxAmount && $weeeHelper->typeOfDisplay($product, array (0, 1, 4))) {
                $minimalPriceTax += $weeeTaxAmount;
                $minimalPriceInclTax += $weeeTaxAmountInclTaxes;
            }
            
            if ($weeeTaxAmount && $weeeHelper->typeOfDisplay($product, 2)) {
                $minimalPriceInclTax += $weeeTaxAmountInclTaxes;
            }

            if ($weeeHelper->typeOfDisplay($product, array (1, 2, 4))) {
                $weeeTaxAttributes = $weeeHelper->getProductWeeeAttributesForDisplay($product);
            }
        }
        
        if ($product->getPriceView()) {
            $priceExcludingTax = 0;
            $weee = array ();
            $priceIncludingTax = 0;
            
            if ($this->_displayBothPrices($product)) {
                $priceExcludingTax = array (
                    'label' => 'Excl. Tax',
                    'value' => $minimalPriceTax,
                );
                
                if ($weeeTaxAmount && $product->getPriceType() == 1 && $weeeHelper->typeOfDisplay($product, array (2, 1, 4))) {
                    foreach ($weeeTaxAttributes as $weeeTaxAttribute) {
                        if ($weeeHelper->typeOfDisplay($product, array (2, 4))) {
                            $amount = $weeeTaxAttribute->getAmount() + $weeeTaxAttribute->getTaxAmount();
                        }
                        else {
                            $amount = $weeeTaxAttribute->getAmount();
                        }
                        
                        $weee[] = array (
                            'name' => $weeeTaxAttribute->getName(),
                            'amount' => $amount,
                        );
                    }
                }
                
                $priceIncludingTax = array (
                    'label' => 'Incl. Tax',
                    'value' => $minimalPriceInclTax,
                );
            }
            else {
                $minimalPrice['display_both_prices'] = false;
                $priceExcludingTax = $taxHelper->displayPriceIncludingTax() ? $minimalPriceInclTax : $minimalPriceTax;
                
                if ($weeeTaxAmount && $product->getPriceType() == 1 && $weeeHelper->typeOfDisplay($product, array (2, 1, 4))) {
                    foreach ($weeeTaxAttributes as $weeeTaxAttribute) {
                        if ($weeeHelper->typeOfDisplay($product, array (2, 4))) {
                            $amount = $weeeTaxAttribute->getAmount() + $weeeTaxAttribute->getTaxAmount();
                        }
                        else {
                            $amount = $weeeTaxAttribute->getAmount();
                        }
                        
                        $weee[] = array (
                            'name' => $weeeTaxAttribute->getName(),
                            'amount' => $amount,
                        );
                    }
                }
                
                if ($weeeHelper->typeOfDisplay($product, 2) && $weeeTaxAmount) {
                    $priceIncludingTax = $minimalPriceInclTax;
                }
            }
            
            $_result['minimal_price']['label'] = 'As low as';
            $_result['minimal_price']['price_excluding_tax'] = $priceExcludingTax;
            $_result['minimal_price']['weee'] = $weee;
            $_result['minimal_price']['price_including_tax'] = $priceIncludingTax;
        }
        else {
            if ($minimalPriceTax <> $maximalPriceTax) {
                $priceExcludingTax = 0;
                $weee = array ();
                $priceIncludingTax = 0;
                
                if ($this->_displayBothPrices($product)) {
                    $priceExcludingTax = array (
                        'label' => 'Excl. Tax',
                        'value' => $minimalPriceTax,
                    );
                    
                    if ($weeeTaxAmount && $product->getPriceType() == 1 && $weeeHelper->typeOfDisplay($product, array (2, 1, 4))) {
                        foreach ($weeeTaxAttributes as $weeeTaxAttribute) {
                            if ($weeeHelper->typeOfDisplay($product, array (2, 4))) {
                                $amount = $weeeTaxAttribute->getAmount() + $weeeTaxAttribute->getTaxAmount();
                            }
                            else {
                                $amount = $weeeTaxAttribute->getAmount();
                            }
                            
                            $weee[] = array (
                                'name' => $weeeTaxAttribute->getName(),
                                'amount' => $amount, true, true,
                            );
                        }
                    }
                    
                    $priceIncludingTax = array (
                        'label' => 'Incl. Tax',
                        'value' => $minimalPriceInclTax,
                    );
                }
                else {
                    $priceExcludingTax = $taxHelper->displayPriceIncludingTax() ? $minimalPriceInclTax : $minimalPriceTax;
                    
                    if ($weeeTaxAmount && $product->getPriceType() == 1 && $weeeHelper->typeOfDisplay($product, array (2, 1, 4))) {
                        foreach ($weeeTaxAttributes as $weeeTaxAttribute) {
                            if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))) {
                                $amount = $weeeTaxAttribute->getAmount() + $weeeTaxAttribute->getTaxAmount();
                            }
                            else {
                                $amount = $weeeTaxAttribute->getAmount();
                            }
                            
                            $weee[] = array (
                                'name' => $weeeTaxAttribute->getName(),
                                'amount' => $amount,
                            );
                        }
                    }
                    
                    if ($weeeHelper->typeOfDisplay($product, 2) && $weeeTaxAmount) {
                        $priceIncludingTax = $minimalPriceInclTax;
                    }
                }
                
                $_result['price_range']['price_from']['price_excluding_tax'] = $priceExcludingTax;
                $_result['price_range']['price_from']['weee'] = $weee;
                $_result['price_range']['price_from']['price_including_tax'] = $priceIncludingTax;
                
                if ($product->getPriceType() == 1) {
                    if ($weeeTaxAmount && $weeeHelper->typeOfDisplay($product, array (0, 1, 4))) {
                        $maximalPriceTax += $weeeTaxAmount;
                        $maximalPriceInclTax += $weeeTaxAmountInclTaxes;
                    }

                    if ($weeeTaxAmount && $weeeHelper->typeOfDisplay($product, 2)) {
                        $maximalPriceInclTax += $weeeTaxAmountInclTaxes;
                    }
                }
                
                $priceExcludingTax = 0;
                $weee = array ();
                $priceIncludingTax = 0;
                
                if ($this->_displayBothPrices($product)) {
                    $priceExcludingTax = array (
                        'label' => 'Excl. Tax',
                        'value' => $maximalPriceTax,
                    );
                    
                    if ($weeeTaxAmount && $product->getPriceType() == 1 && $weeeHelper->typeOfDisplay($product, array (2, 1, 4))) {
                        foreach ($weeeTaxAttributes as $weeeTaxAttribute) {
                            if ($weeeHelper->typeOfDisplay($product, array (2, 4))) {
                                $amount = $weeeTaxAttribute->getAmount() + $weeeTaxAttribute->getTaxAmount();
                            }
                            else {
                                $amount = $weeeTaxAttribute->getAmount();
                            }
                            
                            $weee[] = array (
                                'name' => $weeeTaxAttribute->getName(),
                                'amount' => $amount,
                            );
                        }
                    }
                    
                    $priceIncludingTax = array (
                        'label' => 'Incl. Tax',
                        'value' => $maximalPriceInclTax,
                    );
                }
                else {
                    $priceExcludingTax = $taxHelper->displayPriceIncludingTax() ? $maximalPriceInclTax : $maximalPriceTax;
                    
                    if ($weeeTaxAmount && $product->getPriceType() == 1 && $weeeHelper->typeOfDisplay($product, array (2, 1, 4))) {
                        foreach ($weeeTaxAttributes as $weeeTaxAttribute) {
                            if ($weeeHelper->typeOfDisplay($product, array (2, 4))) {
                                $amount = $weeeTaxAttribute->getAmount() + $weeeTaxAttribute->getTaxAmount();
                            }
                            else {
                                $amount = $weeeTaxAttribute->getAmount();
                            }
                            
                            $weee[] = array (
                                'name' => $weeeTaxAttribute->getName(),
                                'amount' => $amount,
                            );
                        }
                    }
                    
                    if ($weeeHelper->typeOfDisplay($product, 2) && $weeeTaxAmount) {
                        $priceIncludingTax = $maximalPriceInclTax;
                    }
                }
                
                $_result['price_range']['price_to']['price_excluding_tax'] = $priceExcludingTax;
                $_result['price_range']['price_to']['weee'] = $weee;
                $_result['price_range']['price_to']['price_including_tax'] = $priceIncludingTax;
            }
            else {
                $priceExcludingTax = 0;
                $weee = array ();
                $priceIncludingTax = 0;
                
                if ($this->_displayBothPrices($product)) {
                    $priceExcludingTax = array (
                        'label' => 'Excl. Tax',
                        'value' => $minimalPriceTax,
                    );
                    
                    if ($weeeTaxAmount && $product->getPriceType() == 1 && $weeeHelper->typeOfDisplay($product, array (2, 1, 4))) {
                        foreach ($weeeTaxAttributes as $weeeTaxAttribute) {
                            if ($weeeHelper->typeOfDisplay($product, array (2, 4))) {
                                $amount = $weeeTaxAttribute->getAmount() + $weeeTaxAttribute->getTaxAmount();
                            }
                            else {
                                $amount = $weeeTaxAttribute->getAmount();
                            }
                            
                            $weee[] = array (
                                'name' => $weeeTaxAttribute->getName(),
                                'amount' => $amount,
                            );
                        }
                    }
                    
                    $priceIncludingTax = array (
                        'label' => 'Incl. Tax',
                        'value' => $minimalPriceInclTax,
                    );
                }
                else {
                    $priceExcludingTax = $minimalPriceTax;
                    
                    if ($weeeTaxAmount && $product->getPriceType() == 1 && $weeeHelper->typeOfDisplay($product, array (2, 1, 4))) {
                        foreach ($weeeTaxAttributes as $weeeTaxAttribute) {
                            if ($weeeHelper->typeOfDisplay($product, array (2, 4))) {
                                $amount = $weeeTaxAttribute->getAmount() + $weeeTaxAttribute->getTaxAmount();
                            }
                            else {
                                $amount = $weeeTaxAttribute->getAmount();
                            }
                            
                            $weee[] = array (
                                'name' => $weeeTaxAttribute->getName(),
                                'amount' => $amount,
                            );
                        }
                    }
                    
                    if ($weeeHelper->typeOfDisplay($product, 2) && $weeeTaxAmount) {
                        $priceIncludingTax = $minimalPriceInclTax;
                    }
                }
                
                $_result['single_price']['price_excluding_tax'] = $priceExcludingTax;
                $_result['single_price']['weee'] = $weee;
                $_result['single_price']['price_including_tax'] = $priceIncludingTax;
            }
        }
        
        return $_result;
    }
    
    protected function _getBundlePriceView($_product) {
        $_finalPrice = $_product->getFinalPrice();
        $_finalPriceInclTax = $_product->getFinalPrice();
        $_weeeTaxAmount = 0;
        
        if ($_product->getPriceType() == 1) {
            $_weeeTaxAmount = Mage::helper('weee')->getAmount($_product);
            
            if (Mage::helper('weee')->typeOfDisplay($_product, array (1, 2, 4))) {
                $_weeeTaxAttributes = Mage::helper('weee')->getProductWeeeAttributesForDisplay($_product);
            }
        }
        
        $isMAPTypeOnGesture = Mage::helper('catalog')->isShowPriceOnGesture($_product);
        $canApplyMAP = Mage::helper('catalog')->canApplyMsrp($_product);
        
        $_result = array ();
        $_result['can_show_price'] = $_product->getCanShowPrice();
        
        if ($_product->getCanShowPrice() !== false) {
            $_result['price_label'] = 'Price as configured';
            
            if (!$this->_getWithoutPrice()) {
                if (!$isMAPTypeOnGesture && $canApplyMAP) {
                    $_result['price_hide'] = true;
                }
                
                if (Mage::helper('tax')->displayBothPrices()) {
                    $_result['price_excluding_tax'] = array (
                        'label' => 'Excl. Tax',
                        'value' => (!$canApplyMAP) ? $_finalPrice : 0,
                    );
                    
                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array (2, 1, 4))) {
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            if (Mage::helper('weee')->typeOfDisplay($_product, array (2, 4))) {
                                $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                            }
                            else {
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            
                            $_result['price_wee'][] = array (
                                'label' => $_weeeTaxAttribute->getName(),
                                'value' => $amount,
                            );
                        }
                    }
                    
                    $_result['price_including_tax'] = array (
                        'label' => 'Incl. Tax',
                        'value' => (!$canApplyMAP) ? $_finalPriceInclTax : 0,
                    );
                }
                else {
                    $_result['price_excluding_tax'] = (!$canApplyMAP) ? $_finalPrice : 0;
                    
                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && Mage::helper('weee')->typeOfDisplay($_product, array (2, 1, 4))) {
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            if (Mage::helper('weee')->typeOfDisplay($_product, array (2, 4))) {
                                $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                            }
                            else {
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            
                            $_result['price_wee'][] = array (
                                'label' => $_weeeTaxAttribute->getName(),
                                'value' => $amount,
                            );
                        }
                    }
                    
                    $_result['price_including_tax'] = 0;
                }
            }
        }
        
        return $_result;
    }
    
    protected function _getWithoutPrice() {
        return false;
    }

    protected function _displayBothPrices($product) {
        if ($product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC && $product->getPriceModel()->getIsPricesCalculatedByIndex() !== false) {
            return false;
        }
        
        return Mage::getSingleton('tax/config')->getPriceDisplayType($this->_getStore()) == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH;
    }

    protected function _getBundleOptions($product) {
        $typeInstance = $product->getTypeInstance(true);
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $typeInstance->getOptionsCollection($product);
        $selectionCollection = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
        $options = $optionCollection->appendSelections($selectionCollection, false, Mage::helper('catalog/product')->getSkipSaleableCheck());

        return $options;
    }
    
    protected function _showSingle($option) {
        $selections = $option->getSelections();
        $showSingle = (count($selections) == 1 && $option->getRequired());
        
        return $showSingle;
    }
    
    protected function _getSelectionGroupPrice($_selection) {
        return $_selection->getPriceModel()->getGroupPrice($_selection);
    }
    
    protected function _formatPriceString($currentProduct, $formatProduct, $price) {
        $taxHelper = Mage::helper('tax');
        $coreHelper = Mage::helper('core');
        
        if ($currentProduct->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC && $formatProduct) {
            $product = $formatProduct;
        }
        else {
            $product = $currentProduct;
        }
        
        $_result = array ();

        $priceTax = $taxHelper->getPrice($product, $price);
        $priceIncTax = $taxHelper->getPrice($product, $price, true);
        
        $_result = array ();
        $_result['price_tax'] = $priceTax;
        
        if ($taxHelper->displayBothPrices() && $priceTax != $priceIncTax) {
            $_result['price_include_tax'] = array (
                'title' => 'Incl. Tax',
                'value' => $priceIncTax,
            );
        }

        return $_result;
    }
    
    protected function _getBundleSelectionData($showSingle, $product, $option, $selections) {
        $result = array ();
        $isPriceFixed = ($product->getData('price_type') == 1) ? true : false; //- 0 => dynamic, 1 => fixed
        
        if ($showSingle) {
            $result = array (
                'title' => $this->_escapeHtml($selections[0]->getData('name')),
                'price_type' => $this->_getBundleSelectionPriceType($isPriceFixed, $selections[0]),
                'price' => $this->_getBundleSelectionPrice($isPriceFixed, $selections[0]),
                'special_price' => $this->_getBundleSelectionSpecialPrice($selections[0]),
                'group_price' => $this->_getBundleSelectionGroupPrice($isPriceFixed, $selections[0]),
                'selection_id' => $selections[0]->getData('selection_id'),
                'default_value' => $this->_getBundleDefaultValues($product, $option, $selections[0]),
                'is_selected' => false,
            );
        }
        else {
            foreach ($selections as $selection) {
                $result[] = array (
                    'title' => $this->_escapeHtml($selection->getData('name')),
                    'price_type' => $this->_getBundleSelectionPriceType($isPriceFixed, $selection),
                    'price' => $this->_getBundleSelectionPrice($isPriceFixed, $selection),
                    'special_price' => $this->_getBundleSelectionSpecialPrice($selection),
                    'group_price' => $this->_getBundleSelectionGroupPrice($isPriceFixed, $selection),
                    'selection_id' => $selection->getData('selection_id'),
                    'default_value' => $this->_getBundleDefaultValues($product, $option, $selection),
                    'is_selected' => $this->_isSelected($product, $option, $selection),
                );
            }
        }
        
        return $result;
    }
    
    protected function _getBundleSelectionPriceType($isPriceFixed, $selection) {
        return $isPriceFixed ? $selection->getData('selection_price_type') : null;
    }

    protected function _getBundleSelectionPrice($isPriceFixed, $selection) {
        return $isPriceFixed ? $selection->getData('selection_price_value') : $selection->getData('price');
    }

    protected function _getBundleSelectionSpecialPrice($selection) {
        return array (
            'price' => $selection->getData('special_price'),
            'from_date' => $selection->getData('special_from_date'),
            'to_date' => $selection->getData('special_to_date'),
        );
    }
    
    protected function _getBundleSelectionGroupPrice($isPriceFixed, $selection) {
        if ($isPriceFixed) {
            return null;
        }
        
        return $selection->getData('group_price');
    }

    protected function _getBundleDefaultValues($product, $option, $selection) {
        $optionType = $option->getType();
        
//        if (in_array($optionType, array ('select', 'checkbox'))) {
//            $default = $option->getDefaultSelection();
//            $selections = $option->getSelections();
//            $selectedOptions = $this->_getBundleSelectedOptions($product, $option);
//            $inPreConfigured = $product->hasPreconfiguredValues() && $product->getPreconfiguredValues()->getData('bundle_option_qty/' . $option->getId());
//
//            if (empty ($selectedOptions) && $default) {
//                $test = 1;
//                $defaultQty = $default->getSelectionQty() * 1;
//                $canChangeQty = $default->getSelectionCanChangeQty();
//            }
//            elseif (!$inPreConfigured && $selectedOptions && is_numeric($selectedOptions)) {
//                $test = 2;
//                $selectedSelection = $option->getSelectionById($selectedOptions);
//                $defaultQty = $selectedSelection->getSelectionQty() * 1;
//                $canChangeQty = $selectedSelection->getSelectionCanChangeQty();
//            }
//            elseif (!$this->_showSingle($option) || $inPreConfigured) {
//                $test = 3;
//                $defaultQty = $this->_getBundleSelectedQty($product, $option);
//                $canChangeQty = (bool) $defaultQty;
//            }
//            else {
//                $test = 4;
//                $defaultQty = $selections[0]->getSelectionQty() * 1;
//                $canChangeQty = $selections[0]->getSelectionCanChangeQty();
//            }
//        }
//        else {
            //$test = 5;
            $defaultQty = $selection->getSelectionQty() * 1;
            $canChangeQty = $selection->getSelectionCanChangeQty();
        //}

        return array (
            //'test' => $test,
            'default_qty' => $defaultQty,
            'can_change_qty' => $canChangeQty,
        );
    }
    
    protected function _getBundleSelectedOptions($product, $option) {
        $selectedOptions = array ();

        if ($product->hasPreconfiguredValues()) {
            $configValue = $product->getPreconfiguredValues()->getData('bundle_option/' . $option->getId());
            
            if ($configValue) {
                $selectedOptions = $configValue;
            }
            elseif (!$option->getRequired()) {
                $selectedOptions = 'None';
            }
        }
        
        return $selectedOptions;
    }
    
    protected function _getBundleSelectedQty($product, $option) {
        if ($product->hasPreconfiguredValues()) {
            $selectedQty = (float) $product->getPreconfiguredValues()->getData('bundle_option_qty/' . $option->getId());
            
            if ($selectedQty < 0) {
                $selectedQty = 0;
            }
        }
        else {
            $selectedQty = 0;
        }

        return $selectedQty;
    }
    
    protected function _isSelected($product, $option, $selection) {
        $selectedOptions = $this->_getSelectedOptions($product, $option);
        
        if (is_numeric($selectedOptions)) {
            return ($selection->getSelectionId() == $this->_getSelectedOptions());
        }
        elseif (is_array($selectedOptions) && !empty ($selectedOptions)) {
            return in_array($selection->getSelectionId(), $this->_getSelectedOptions());
        }
        elseif ($selectedOptions == 'None') {
            return false;
        }
        else {
            return ($selection->getIsDefault() && $selection->isSaleable());
        }
    }
    
    public function retrieve($productId, $storeId) {
        $product = Mage::helper('catalog/product')->getProduct($productId, $storeId);
        $product = $this->_prepareProductForResponse($product, true);
        
        if (!$product->getId()) {
            return false;
        }
        
        return $this->retrieveResponse($product);
    }
    
    protected function retrieveResponse($product) {
        $attributeAllowArr = array ('entity_id', 'group_price', 'tier_price');
        $attributeTextArr = array ('brand', 'brands', 'ship_by', 'sold_by');
        $attributeDetailedInfoArr = array ('description', 'additional', 'how_to_use', 'nutrition_fact', 'size_chart', 'more_detail', 'additional_info');
        $result = array ();
        
        foreach ($product->getData() as $k => $v) {
            if (!in_array($k, array_merge($attributeAllowArr, $attributeDetailedInfoArr))) {
                continue;
            }
            
            if (in_array($k, $attributeTextArr)) {
                if ($k == 'brands') {
                    $result[$k] = $this->_getBrandsUrl($v);
                }
                else {
                    $result[$k] = $product->getAttributeText($k);
                }
            }
            elseif (in_array($k, $attributeDetailedInfoArr)) {
                $result['detailed_info'][$k] = $v;
            }
            elseif ($k == 'stock_data') {
                continue;
            }
            else {
                $result[$k] = $v;
            }
        }
        
        if ($result) {
            $result['attribute_config'] = $this->_getAttributeConfig($product);
            $result['attribute_bundle'] = $this->_getAttributeBundle($product);
            $result['review'] = $this->_getProductReview($product->getId());
            $result['images'] = array (
                'default' => $this->_getImageResize($product, $product->getImage()),
                'data' => $this->_getImage($product),
            );
        }
        
        return $result;
    }
}
