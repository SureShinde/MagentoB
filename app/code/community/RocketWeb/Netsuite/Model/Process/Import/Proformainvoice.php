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

class RocketWeb_Netsuite_Model_Process_Import_Proformainvoice extends RocketWeb_Netsuite_Model_Process_Import_Abstract {
    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_PROFORMAINVOICES;
    }

    public function getRecordType() {
        return 'proformainvoice';
    }

    public function isActive() {
        return true;
    }

    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::PROFORMAINVOICE_IMPORTED;
    }

    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::PROFORMAINVOICE_DELETED;
    }

    public function processProforma($proformaBody) {
        $arr_proformaBody = explode('|', $proformaBody);
        $proformaBody = $arr_proformaBody[2];
        $data = unserialize($proformaBody);

        Mage::helper('rocketweb_netsuite/mapper_proformainvoice')->getMagentoFormatFromProforma($data);
    }
    
    public function process(Record $invoice, $queueData = NULL) {
        
    }

    public function isAlreadyImported($internalid, $netsuiteUpdateDatetime) {
        $shipmentCollection = Mage::getModel('sales/order_invoice')->getCollection();
        $shipmentCollection->addFieldToFilter('netsuite_internal_id', $internalid);
        $shipmentCollection->addFieldToFilter('last_import_date', array ('gteq' => $netsuiteUpdateDatetime));
        $shipmentCollection->load();
        
        if ($shipmentCollection->count()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function isMagentoImportable($createdfrom) {
        if (is_null($createdfrom)) {
            return false;
        }
        
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $createdfrom);
        $magentoOrders->load();
        
        if (!$magentoOrders->getSize()) {
            return false;
        }
        else {
            return true;
        }
    }
}