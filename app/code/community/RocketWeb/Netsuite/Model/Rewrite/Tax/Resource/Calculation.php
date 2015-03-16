<?php
class RocketWeb_Netsuite_Model_Rewrite_Tax_Resource_Calculation extends Mage_Tax_Model_Resource_Calculation {
    protected function _getRates($request)
    {
        $city = $request->getCity(); //custom

        // Extract params that influence our SELECT statement and use them to create cache key
        $storeId = Mage::app()->getStore($request->getStore())->getId();
        $customerClassId = $request->getCustomerClassId();
        $countryId = $request->getCountryId();
        $regionId = $request->getRegionId();
        $postcode = $request->getPostcode();

        // Process productClassId as it can be array or usual value. Form best key for cache.
        $productClassId = $request->getProductClassId();
        $ids = is_array($productClassId) ? $productClassId : array($productClassId);
        foreach ($ids as $key => $val) {
            $ids[$key] = (int) $val; // Make it integer for equal cache keys even in case of null/false/0 values
        }
        $ids = array_unique($ids);
        sort($ids);
        $productClassKey = implode(',', $ids);

        // Form cache key and either get data from cache or from DB
        $cacheKey = implode('|', array($storeId, $customerClassId, $productClassKey, $countryId, $regionId, $postcode));

        if (!isset($this->_ratesCache[$cacheKey])) {
            // Make SELECT and get data
            $select = $this->_getReadAdapter()->select();
            $select
                ->from(array('main_table' => $this->getMainTable()),
                    array('tax_calculation_rate_id',
                        'tax_calculation_rule_id',
                        'customer_tax_class_id',
                        'product_tax_class_id'
                    )
                )
                ->where('customer_tax_class_id = ?', (int)$customerClassId);
            if ($productClassId) {
                $select->where('product_tax_class_id IN (?)', $productClassId);
            }
            $ifnullTitleValue = $this->_getReadAdapter()->getCheckSql(
                'title_table.value IS NULL',
                'rate.code',
                'title_table.value'
            );
            $ruleTableAliasName = $this->_getReadAdapter()->quoteIdentifier('rule.tax_calculation_rule_id');
            $select
                ->join(
                    array('rule' => $this->getTable('tax/tax_calculation_rule')),
                    $ruleTableAliasName . ' = main_table.tax_calculation_rule_id',
                    array('rule.priority', 'rule.position'))
                ->join(
                    array('rate' => $this->getTable('tax/tax_calculation_rate')),
                    'rate.tax_calculation_rate_id = main_table.tax_calculation_rate_id',
                    array(
                        'value' => 'rate.rate',
                        'rate.tax_country_id',
                        'rate.tax_region_id',
                        'rate.tax_postcode',
                        'rate.tax_calculation_rate_id',
                        'rate.code'
                    ))
                ->joinLeft(
                    array('title_table' => $this->getTable('tax/tax_calculation_rate_title')),
                    "rate.tax_calculation_rate_id = title_table.tax_calculation_rate_id "
                        . "AND title_table.store_id = '{$storeId}'",
                    array('title' => $ifnullTitleValue))
                ->where('rate.tax_country_id = ?', $countryId)
                ->where("rate.tax_region_id IN(?)", array(0, (int)$regionId));
            $postcodeIsNumeric = is_numeric($postcode);
            $postcodeIsRange = is_string($postcode) && preg_match('/^(.+)-(.+)$/', $postcode, $matches);
            if ($postcodeIsRange) {
                $zipFrom = $matches[1];
                $zipTo = $matches[2];
            }

            if ($postcodeIsNumeric || $postcodeIsRange) {
                $selectClone = clone $select;
                $selectClone->where('rate.zip_is_range IS NOT NULL');
            }
            $select->where('rate.zip_is_range IS NULL');

            if ($postcode != '*' || $postcodeIsRange) {
                $select
                    ->where("rate.tax_postcode IS NULL OR rate.tax_postcode IN('*', '', ?)",
                        $postcodeIsRange ? $postcode : $this->_createSearchPostCodeTemplates($postcode));
                if ($postcodeIsNumeric) {
                    $selectClone
                        ->where('? BETWEEN rate.zip_from AND rate.zip_to', $postcode);
                } else if ($postcodeIsRange) {
                    $selectClone->where('rate.zip_from >= ?', $zipFrom)
                        ->where('rate.zip_to <= ?', $zipTo);
                }
            }

            //customization
            if (!empty($city)) {
                $select->where("LOWER(rate.tax_city) IN ('*', '', ?)",array(strtolower($city)));
            }
            //customization end

            /**
             * @see ZF-7592 issue http://framework.zend.com/issues/browse/ZF-7592
             */
            if ($postcodeIsNumeric || $postcodeIsRange) {
                $select = $this->_getReadAdapter()->select()->union(
                    array(
                        '(' . $select . ')',
                        '(' . $selectClone . ')'
                    )
                );
            }

            if(!empty($city)) {
                $select->order('priority ' . Varien_Db_Select::SQL_ASC)
                    ->order('tax_calculation_rule_id ' . Varien_Db_Select::SQL_ASC)
                    ->order('tax_country_id ' . Varien_Db_Select::SQL_DESC)
                    ->order('tax_region_id ' . Varien_Db_Select::SQL_DESC)
                    ->order('tax_postcode ' . Varien_Db_Select::SQL_DESC)
                    ->order('value ' . Varien_Db_Select::SQL_DESC);
            }
            else {
                $select->order('value ' . Varien_Db_Select::SQL_ASC);
                $select->limit(1);
            }

            //customization - if city is specified but there are no matches, retry for zip code
            $result  = $this->_getReadAdapter()->fetchAll($select);
            if(!empty($city) && (!$result || count($result) == 0)) {
                $request->setCity('');
                return $this->_getRates($request);
            }
            $this->_ratesCache[$cacheKey] = $result;
            //customization end
        }

        return $this->_ratesCache[$cacheKey];
    }
}