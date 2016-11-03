<?php
/**
 * Orami
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.orami.co.id
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2016 Orami Dev (http://www.orami.co.id)
 * @author     Orami.
 * @license    http://www.orami.co.id
 */

class RocketWeb_Netsuite_Model_Process_Import_Requestorder extends RocketWeb_Netsuite_Model_Process_Import_Abstract {
    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_REQUESTORDER;
    }

    public function getRecordType() {
        return 'requestorder';
    }

    public function isActive() {
        return true;
    }

    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::REQUESTORDER_IMPORTED;
    }

    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::REQUESTORDER_DELETED;
    }
    
    public function process(Record $invoice, $queueData = NULL) {
        
    }

    public function processRequestOrder($requestOrderBody) {
        $arr_requestOrderBody = explode('|', $requestOrderBody);
        $requestOrderBody = $arr_requestOrderBody[2];
        $data = unserialize($requestOrderBody);

        Mage::helper('rocketweb_netsuite/mapper_requestorder')->processMagentoFromROData($data);
    }

    public function isAlreadyImported($internalid, $netsuiteUpdateDatetime) {
        $soCollection = Mage::getModel('sales/order')->getCollection();
        $soCollection->addFieldToFilter('netsuite_internal_id', $internalid);
        $soCollection->addFieldToFilter('last_import_date', array ('gteq' => $netsuiteUpdateDatetime));
        $soCollection->load();
        
        if ($soCollection->count()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function isMagentoImportable($internalid) {
        if (is_null($internalid)) {
            return false;
        }
        
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $internalid);
        $magentoOrders->load();
        
        if (!$magentoOrders->getSize()) {
            return false;
        }
        else {
            $magentoOrder = $magentoOrders->getFirstItem();
            // ignore status complete, closed, canceled or holded
            if ($magentoOrder->getState() == 'complete' || $magentoOrder->getState() == 'closed' || $magentoOrder->getState() == 'canceled' ||
                $magentoOrder->getState() == 'holded')
                return false;

            return true;
        }
    }
}