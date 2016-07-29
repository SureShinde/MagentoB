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

    public function isMagentoImportable($salesOrder) {
        /** @var SalesOrder $salesOrder */

        //Exclude the orders that have the same creation and last modified date as:
        //  - they are already present in Magento in the same format (Magento sent them to Net Suite)
        //  - they are not part of Magento
        if ($salesOrder->basic->lastModifiedDate[0]->searchValue == $salesOrder->basic->dateCreated[0]->searchValue) {
            return false;
        }

        if (is_null($salesOrder->basic->customFieldList->customField[0]->searchValue->internalId) || $salesOrder->basic->customFieldList->customField[0]->searchValue->internalId == '')
            $netsuiteOrderId = $salesOrder->basic->internalId[0]->searchValue->internalId;
        else
            $netsuiteOrderId = $salesOrder->basic->customFieldList->customField[0]->searchValue->internalId;

        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id',$netsuiteOrderId);
        $magentoOrders->load();
        if(!$magentoOrders->getSize()) {
            return false;
        }
        else {
            return true;
        }
    }

    public function isMagentoImportableOld(Record $salesOrder) {
        /** @var SalesOrder $salesOrder */

        //Exclude the orders that have the same creation and last modified date as:
        //  - they are already present in Magento in the same format (Magento sent them to Net Suite)
        //  - they are not part of Magento
        if ($salesOrder->lastModifiedDate == $salesOrder->createdDate) {
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

    public function isAlreadyImported(SearchRow $record) {
        if (is_null($record->basic->customFieldList->customField[0]->searchValue->internalId) || $record->basic->customFieldList->customField[0]->searchValue->internalId == '')
            $netsuiteOrderId = $record->basic->internalId[0]->searchValue->internalId;
        else
            $netsuiteOrderId = $record->basic->customFieldList->customField[0]->searchValue->internalId;
        
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('netsuite_internal_id',$netsuiteOrderId);
        $netsuiteUpdateDatetime = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->basic->lastModifiedDate[0]->searchValue);
        $orderCollection->addFieldToFilter('last_import_date',array('gteq'=>$netsuiteUpdateDatetime));
        $orderCollection->load();
        if($orderCollection->count()) return true;
        else return false;
    }

    public function isAlreadyImportedOld(Record $record) {
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

    public function process(Record $netsuiteOrder, $queueData = null) {
        $magentoOrder = Mage::helper('rocketweb_netsuite/mapper_order')->getMagentoFormat($netsuiteOrder);

        $readytoprocess = false;
        $paymentmethod = 0;

        if (!is_null($netsuiteOrder->customFieldList->customField))
        {
            foreach ($netsuiteOrder->customFieldList->customField as $customField) {
                if ($customField->internalId == 'custbody_orderfullypaid') {
                    $readytoprocess = $customField->value->internalId;
                }

                if ($customField->internalId == 'custbody_paymentmethod') {
                    $paymentmethod = $customField->value->internalId;
                }
            }
        }

        // if SO ready to process and payment method is COD
        if ($readytoprocess && $paymentmethod == '12')
            $magentoOrder->setStatus('processing_cod');

        $magentoOrder->setNetsuiteInternalId($netsuiteOrder->internalId);
        $magentoOrder->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($netsuiteOrder->lastModifiedDate));
        $magentoOrder->getResource()->save($magentoOrder);

        return true;
    }
}
