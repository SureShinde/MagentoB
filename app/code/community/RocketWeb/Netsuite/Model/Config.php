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
class RocketWeb_Netsuite_Model_Config extends Mage_Core_Model_Config_Data {
    CONST XML_PATH_NETSUITE = 'rocketweb_netsuite';
    protected $_config_cache = array();

    const NETSUITE_ORDER_STATUS_PENDING_APPROVAL = '_pendingApproval';
    const NETSUITE_ORDER_STATUS_PENDING_FULFILLMENT = '_pendingFulfillment';
    const NETSUITE_ORDER_STATUS_CANCELED = '_cancelled';
    const NETSUITE_ORDER_STATUS_PARTIAL_FULFILLED = '_partiallyFulfilled';
    const NETSUITE_ORDER_STATUS_PARTIAL_FULFILLED_PENDING_BILLING = '_pendingBillingPartFulfilled';
    const NETSUITE_ORDER_STATUS_PENDING_BILLING = '_pendingBilling';
    const NETSUITE_ORDER_STATUS_BILLED = '_fullyBilled';
    const NETSUITE_ORDER_STATUS_CLOSED = '_closed';
    const NETSUITE_ORDER_STATUS_UNDEFINED = '_undefined';

    public function convertDefaultMapProductColumns($store_id = null) {
        $ret = array();
        $default_map_product_columns = Mage::getStoreConfig(self::XML_PATH_NETSUITE.'/products/default_field_map', $store_id);
        foreach ($default_map_product_columns as $arr) {
            $ret[] = array(
                'magento' => $arr['magento'],
                'netsuite_field_type' => $arr['netsuite_field_type'],
                'netsuite' => $arr['netsuite'],
            );
        }
        return $ret;
    }

    public function getConfigVarMapProductColumns($key, $store_id = null, $section = 'settings') {
        $ret = Mage::getStoreConfig(self::XML_PATH_NETSUITE.'/'.$section.'/'.$key, $store_id);
        if (empty($ret)) {
            $ret = $this->convertDefaultMapProductColumns($store_id);
        }
        else {
            $ret = unserialize($ret);
        }

        return $ret;
    }

    public function convertDefaultMapOrderstatuses($store_id = null) {
        $ret = array();
        $default_map_status_columns = Mage::getStoreConfig(self::XML_PATH_NETSUITE.'/orders/default_status_map', $store_id);
        foreach ($default_map_status_columns as $arr) {
            $ret[] = array(
                'netsuite_status' => $arr['netsuite_status'],
                'magento_status' => $arr['magento_status'],
            );
        }
        return $ret;
    }

    public function getConfigVarMapOrderStatusColumns($key, $store_id = null, $section = 'settings') {
        $ret = Mage::getStoreConfig(self::XML_PATH_NETSUITE.'/'.$section.'/'.$key, $store_id);
        if (empty($ret)) {
            $ret = $this->convertDefaultMapOrderstatuses($store_id);
        }
        else {
            $ret = unserialize($ret);
        }

        return $ret;
    }

}