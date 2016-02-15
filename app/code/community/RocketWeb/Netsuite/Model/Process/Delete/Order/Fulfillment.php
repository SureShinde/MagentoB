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

class RocketWeb_Netsuite_Model_Process_Delete_Order_Fulfillment extends RocketWeb_Netsuite_Model_Process_Delete_Abstract {
    public function processDeleteOperation(DeletedRecord $record) {
        if(!$record->record->internalId) {
            return;
        }
        $shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentCollection->addFieldToFilter('netsuite_internal_id',$record->record->internalId);
        if($shipmentCollection->getSize()) {
            $shipmentCollection->getFirstItem()->delete();
        }
    }
}