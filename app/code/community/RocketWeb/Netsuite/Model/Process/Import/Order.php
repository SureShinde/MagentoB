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

class RocketWeb_Netsuite_Model_Process_Import_Order extends RocketWeb_Netsuite_Model_Process_Import_Abstract {
    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_ORDER_CHANGES;
    }

    public function isMagentoImportable(Record $salesOrder) {
        /** @var SalesOrder $salesOrder */

        //Exclude the orders that have the same creation and last modified date as:
        //  - they are already present in Magento in the same format (Magento sent them to Net Suite)
        //  - they are not part of Magento
        if($salesOrder->lastModifiedDate == $salesOrder->createdDate) {
            return false;
        }

        //check if the order already exists in Magento. If not, we do not care about its updates as the order is not related to the store.
        $netsuiteOrderId = $salesOrder->internalId;
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id',$netsuiteOrderId);
        $magentoOrders->load();
        if(!$magentoOrders->getSize()) {
            return false;
        }
        else {
            return true;
        }
    }

    public function isAlreadyImported(Record $record) {
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('netsuite_internal_id',$record->internalId);
        $netsuiteUpdateDatetime = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->lastModifiedDate);
        $orderCollection->addFieldToFilter('last_import_date',array('gteq'=>$netsuiteUpdateDatetime));
        $orderCollection->load();
        if($orderCollection->count()) return true;
        else return false;
    }

    public function getRecordType() {
        return RecordType::salesOrder;
    }

    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::ORDER_IMPORTED;
    }

    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::ORDER_DELETED;
    }

    public function isActive() {
        return true;
    }

    public function process(Record $netsuiteOrder) {
        $magentoOrder = Mage::helper('rocketweb_netsuite/mapper_order')->getMagentoFormat($netsuiteOrder);
        $magentoOrder->setNetsuiteInternalId($netsuiteOrder->internalId);
        $magentoOrder->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($netsuiteOrder->lastModifiedDate));
        $magentoOrder->getResource()->save($magentoOrder);

        //update the order grid
        $dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid');
        $query = "UPDATE $tableName SET grand_total = {$magentoOrder->getGrandTotal()}, base_grand_total = {$magentoOrder->getGrandTotal()}, status = '{$magentoOrder->getStatus()}' WHERE entity_id = {$magentoOrder->getId()}";
        $dbConnection->query($query);
    }
}
