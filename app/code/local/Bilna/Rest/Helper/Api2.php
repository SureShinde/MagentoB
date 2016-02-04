<?php
/**
 * Description of Bilna_Rest_Helper_Api2
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Helper_Api2 extends Mage_Core_Helper_Abstract {
    /**
     * Product Images size
     */
    protected $_imgThumbnail = 72;
    protected $_imgHorizontal = 110;
    protected $_imgVertical = 151;
    protected $_imgDetail = 225;
    
    public function retrieveCollectionResponse($products, $attributeFilter) {
        if (!$products) {
            return false;
        }
        
        $result = array ();
        $result[0] = array ('total_record' => $products->getSize());
        
        foreach ($products as $key => $row) {
            $product = $this->_prepareProductForResponse($this->_getProduct($row->getId()));
            
            foreach ($product->getData() as $k => $v) {
                if (!in_array($k, $attributeFilter)) {
                    continue;
                }
                
                $data[$key] = $key;
                $attributeTextArr = array ('brand', 'ship_by', 'sold_by');
                
                if (in_array($k, $attributeTextArr)) {
                    $result[$key][$k] = $row->getAttributeText($k);
                }
                elseif ($k == 'stock_data') {
                    $result[$key][$k] = $this->_getStockDataConfig($v);
                }
                else {
                    $result[$key][$k] = $v;
                }
            }
            
            $result[$key]['attribute_bundle'] = $this->_getAttributeBundle($product);
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
    
    protected function _prepareProductForResponse(Mage_Catalog_Model_Product $product) {
        $pricesFilterKeys = array ('price_id', 'all_groups', 'website_price');
        $groupPrice = $product->getData('group_price');
        $tierPrice = $product->getData('tier_price');
        
        $product->setData('group_price', $this->_filterOutArrayKeys($groupPrice, $pricesFilterKeys, true));
        $product->setData('tier_price', $this->_filterOutArrayKeys($tierPrice, $pricesFilterKeys, true));

        $stockData = $product->getStockItem()->getData();
        $stockDataFilterKeys = array ('item_id', 'product_id', 'stock_id', 'low_stock_date', 'type_id',
            'stock_status_changed_auto', 'stock_status_changed_automatically', 'product_name', 'store_id',
            'product_type_id', 'product_status_changed', 'product_changed_websites',
            'use_config_enable_qty_increments'
        );
        
        $product->setData('stock_data', $this->_filterOutArrayKeys($stockData, $stockDataFilterKeys));
        $product->setData('product_type_name', $product->getTypeId());
        
        return $product;
    }
    
    protected function _getProduct($_productId) {
        $productHelper = Mage::helper('catalog/product');
        $_product = $productHelper->getProduct($_productId, $this->_getStoreId());

        if (!$_product->getId()) {
            return null;
        }

        if (!$productHelper->canShow($_product)) {
            return null;
        }
        
        return $_product;
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
    
    protected function _getAttributeBundle($product) {
        if ($product->getData('type_id') != 'bundle' || !$product->isSaleable()) {
            return null;
        }
        
        $bundle = array ();
        //$bundle['price'] = $this->_getBundlePrice($product);
        //$bundle['price_view'] = $this->_getBundlePriceView($product);
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
    
    protected function _escapeHtml($data, $allowedTags = null) {
        return Mage::helper('core')->escapeHtml($data, $allowedTags);
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
        $defaultQty = $selection->getSelectionQty() * 1;
        $canChangeQty = $selection->getSelectionCanChangeQty();

        return array (
            'default_qty' => $defaultQty,
            'can_change_qty' => $canChangeQty,
        );
    }
    
    protected function _isSelected($product, $option, $selection) {
        $selectedOptions = $this->_getSelectedOptions($product, $option);
        
        if (is_numeric($selectedOptions)) {
            return ($selection->getSelectionId() == $selectedOptions);
        }
        elseif (is_array($selectedOptions) && !empty ($selectedOptions)) {
            return in_array($selection->getSelectionId(), $selectedOptions);
        }
        elseif ($selectedOptions == 'None') {
            return false;
        }
        else {
            return ($selection->getIsDefault() && $selection->isSaleable());
        }
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
    
    protected function _getProductReview($productId) {
        $review = Mage::getModel('review/review_summary')->setStoreId($this->_getStoreId())->load($productId);
        $result = array (
            'reviews_count' => $review->getData('reviews_count'),
            'rating_summary' => $review->getData('rating_summary'),
        );
        
        return $result;
    }
    
    protected function _resizeImage($product, $imageFile, $size) {
        return (string) Mage::helper('catalog/image')->init($product, 'image', $imageFile)->resize($size);
    }
    
    protected function _getStore() {
        return Mage::app()->getStore();
    }
    
    protected function _getStoreId() {
        return Mage::app()->getStore()->getId();
    }
    
    protected function _getWebsiteId() {
        return Mage::app()->getStore()->getWebsiteId();
    }
}
