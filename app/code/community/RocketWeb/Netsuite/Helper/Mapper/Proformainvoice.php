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
 * @copyright  Copyright (c) 2013 Orami (http://www.orami.co.id)
 * @author     Orami
 * @license    http://www.orami.co.id
 */

class RocketWeb_Netsuite_Helper_Mapper_Proformainvoice extends RocketWeb_Netsuite_Helper_Mapper {
    
    public function getMagentoFormatFromProforma($proformaData) {
        $proformaInvoiceInternalId = $proformaData['internalid'];
        $lastModifiedDate = $proformaData['lastmodifieddate'];
        $magentoInvoice = $this->getMagentoInvoiceFromInternalId($proformaInvoiceInternalId);
        
        if (!$magentoInvoice) {
            $netsuiteOrderId = $proformaData['createdfrom'];
            $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
            $magentoOrder = $magentoOrders->getFirstItem(); // @var Mage_Sales_Model_Order $magentoOrder

            if (!is_object($magentoOrder) || !$magentoOrder->getId()) {
                throw new Exception("Order with netsuite internal id {$netsuiteOrderId} not found in Magento!");
            }

            $magentoInvoice = $this->createMagentoInvoice($magentoOrder, $proformaInvoiceInternalId, $lastModifiedDate);
        }
        
        return $magentoInvoice;
    }

    protected function getMagentoInvoiceFromInternalId($internalid) {
        $invoiceCollection = Mage::getModel('sales/order_invoice')->getCollection();
        $invoiceCollection->addFieldToFilter('netsuite_internal_id', $internalid);
        
        if ($invoiceCollection->count()) {
            return $invoiceCollection->getFirstItem();
        }
        
        return null;
    }

    protected function createMagentoInvoice(Mage_Sales_Model_Order $magentoOrder, $netsuiteInvoiceInternalId, $lastModifiedDate) {
        
        /**
         * Check shipment create availability
         */
        if (!$magentoOrder->canInvoice()) {
           throw new Exception("{$magentoOrder->getId()}: Cannot do invoice for this order!");
        }

        /**
         * check registry skip invoice export
         */
        if (!Mage::registry('skip_invoice_export_queue_push')) {
            Mage::register('skip_invoice_export_queue_push', 1);
        }
        
        $magentoInvoice = $magentoOrder->prepareInvoice();

        $baseGrandTotal = $magentoOrder->getBaseGrandTotal();
        $grandTotal = $magentoOrder->getGrandTotal();
        
        if ($magentoInvoice) {
            $magentoInvoice->register();
            $magentoInvoice->addComment("Create Invoice from Netsuite #{$netsuiteInvoiceInternalId}");
            $magentoInvoice->getOrder()->setIsInProcess(true);
            $magentoInvoice->setGrandTotal($grandTotal);
            $magentoInvoice->setBaseGrandTotal($baseGrandTotal);

            $magentoInvoice->getOrder()->setTotalPaid($grandTotal);
            $magentoInvoice->getOrder()->setBaseTotalPaid($baseGrandTotal);
            //$magentoInvoice->getOrder()->save();
            
            try {
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($magentoInvoice)
                    ->addObject($magentoInvoice->getOrder())
                    ->save();

                $magentoInvoice->setEmailSent(true); 
                $magentoInvoice->setNetsuiteInternalId($netsuiteInvoiceInternalId);
                $magentoInvoice->setLastImportDate($lastModifiedDate);
                $magentoInvoice->save();

                // we have to load based on invoice ID again, because if we send email first, if we use
                // Bilna credit, it won't appear in the email
                // if we load again, then send email, if there is Bilna credit, it will appear
                $invoice = Mage::getModel('sales/order_invoice')->load($magentoInvoice->getId());
                $invoice->sendEmail();
            
                return true;
                //return $magentoInvoice;
            }
            catch (Mage_Core_Exception $e) {
                throw new Exception("{$magentoOrder->getId()}: {$e->getMessage()}");
            }
        }
        
        return null;
    }

    public function getProformaRequest()
    {
        // for header purposes
        $import_config_same = (int) Mage::getStoreConfig('rocketweb_netsuite/connection_import/same');
        $nlauth_account = Mage::getStoreConfig('rocketweb_netsuite/general/account_id');
        $ns_host = Mage::getStoreConfig('rocketweb_netsuite/general/host');

        if ( $import_config_same == 1 )
        {
            $nlauth_email = Mage::getStoreConfig('rocketweb_netsuite/general/email');
            $nlauth_signature = Mage::getStoreConfig('rocketweb_netsuite/general/password');
            $nlauth_role = Mage::getStoreConfig('rocketweb_netsuite/general/role_id');
        }
        else
        {
            $nlauth_email = Mage::getStoreConfig('rocketweb_netsuite/connection_import/email');
            $nlauth_signature = Mage::getStoreConfig('rocketweb_netsuite/connection_import/password');
            $nlauth_role = Mage::getStoreConfig('rocketweb_netsuite/connection_import/role_id');
        }

        // check whether the url is sandbox or not
        if (strpos($ns_host, 'sandbox') !== false)
        {
            $ns_rest_host = "https://rest.sandbox.netsuite.com";
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/proformainvoice/sandbox_import_rst_id');
        }
        else
        {
            $ns_rest_host = "https://rest.netsuite.com";
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/proformainvoice/production_import_rst_id');
        }

        if (!$scriptId)
            return false;

        $importHelper = Mage::helper('rocketweb_netsuite/queue');
        $lastProformaInvoiceUpdateDate = $importHelper->getLastUpdateAccessDateSpecificEntity($importHelper::NETSUITE_IMPORT_PROFORMAINVOICE_QUEUE);
        $lastProformaInvoiceUpdateDate = Mage::getModel('core/date')->date('j/n/Y g:i A', strtotime($lastProformaInvoiceUpdateDate));

        // variables to be posted to proforma
        $vars = array(
            'last_modified_date' => $lastProformaInvoiceUpdateDate
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($ns_rest_host . "/app/site/hosting/restlet.nl?script=$scriptId&deploy=1"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vars));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = 'Authorization: NLAuth nlauth_account='.$nlauth_account.',nlauth_email='.$nlauth_email.',nlauth_signature='.$nlauth_signature.',nlauth_role='.$nlauth_role;
        //$headers[] = 'Accept-Encoding: gzip, deflate';
        //$headers[] = 'Accept-Language: en-US,en;q=0.5';
        //$headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Content-Type: application/json;';
        $headers[] = 'User-Agent-x: SuiteScript-Call';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);

        curl_close($ch);

        $server_output = json_decode($server_output, true);
        $proforma_import_log_file = 'proforma_invoice.log';

        if ($server_output['status'] == 'success')
        {
            // log the internal ids into log file
            Mage::log(date("Y-m-d H:i:s"), null, $proforma_import_log_file);
            foreach($server_output['result'] as $record)
                Mage::log($record['internalid'], null, $proforma_import_log_file);
            return $server_output['result'];
        }
        else
        if ($server_output['status'] == 'error')
        {
            Mage::log(date("Y-m-d H:i:s"), null, $proforma_import_log_file);
            Mage::log($server_output['msg'], null, $proforma_import_log_file);
            return $server_output['msg'];
        }
    }
    
}