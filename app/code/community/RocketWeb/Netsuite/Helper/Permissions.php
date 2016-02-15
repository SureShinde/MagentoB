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
class RocketWeb_Netsuite_Helper_Permissions extends Mage_Core_Helper_Abstract {
    const SEND_ORDERS = 'send_orders';
    const SEND_INVOICES = 'send_invoices';
    const GET_ORDER_CHANGES = 'get_order_changes';
    const GET_SHIPMENTS = 'get_shipments';
    const GET_CASH_SALES = 'get_cash_sales';
    const GET_STOCK_UPDATES = 'get_stock_updates';
    const GET_PRODUCTS = 'get_products';
    const GET_CREDITMEMO = 'get_creditmemo';

    public function isFeatureEnabled($featureCode) {
        $systemVar = 'rocketweb_netsuite/enable_disable_features/';

        switch($featureCode) {
            case self::SEND_ORDERS:
                $systemVar.='send_orders';
                break;
            case self::SEND_INVOICES:
                $systemVar.='send_invoices';
                break;
            case self::GET_ORDER_CHANGES:
                $systemVar.='get_order_changes';
                break;
            case self::GET_SHIPMENTS:
                $systemVar.='get_shipments';
                break;
            case self::GET_CASH_SALES:
                $systemVar.='get_cash_sales';
                break;
            case self::GET_STOCK_UPDATES:
                $systemVar.='get_stock_updates';
                break;
            case self::GET_PRODUCTS:
                $systemVar.='get_products';
                break;
            case self::GET_CREDITMEMO:
                $systemVar .= 'get_creditmemo';
                break;
            default:
                $systemVar.='INVALID';
                break;
        }

        return Mage::getStoreConfig($systemVar);
    }
}