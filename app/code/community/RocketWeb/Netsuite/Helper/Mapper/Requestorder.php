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

class RocketWeb_Netsuite_Helper_Mapper_Requestorder extends RocketWeb_Netsuite_Helper_Mapper {
    
    public function processMagentoFromROData($requestOrderData) {
        $roInternalId = $requestOrderData['internalid'];
        $lastModifiedDate = $requestOrderData['lastmodifieddate'];
        $soReadyToProcess = $requestOrderData['soreadytoprocess'];
        $grandTotal = $requestOrderData['grandtotal'];
        $cancelStatus = $requestOrderData['cancelstatus'];
        $paymentMethod = $requestOrderData['paymethod'];

        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $roInternalId);
        $magentoOrder = $magentoOrders->getFirstItem(); // @var Mage_Sales_Model_Order $magentoOrder

        if (!is_object($magentoOrder) || !$magentoOrder->getId()) {
            throw new Exception("Order with netsuite internal id {$netsuiteOrderId} not found in Magento!");
        }

        $magentoOrder->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($lastModifiedDate));

        // if cancel status is True, then we have to cancel the Magento Order
        if ($cancelStatus == 'T')
        {
            $request_order_cancel_log_file = 'request_order_cancel.log';

            if ($magentoOrder->canCancel())
            {
                Mage::log($magentoOrder->getNetsuiteInternalId() . ' is cancelled', null, $request_order_cancel_log_file);

                $magentoOrder->cancel();
                $magentoOrder->setStatus('canceled');
                $magentoOrderHistory = $magentoOrder->addStatusHistoryComment('');
                $magentoOrderHistory->setIsCustomerNotified(true);
                $magentoOrder->save();

                // refunding points
                $orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $roInternalId);
                $order  = $orders->getFirstItem();

                Mage::helper('rocketweb_netsuite/mapper_order')->cancelAndRefundPoint($order);

                return;
            }
        }
        
        // if so ready to process is True, then check whether there is an invoice
        if ($soReadyToProcess == 'T')
        {
            if ($paymentMethod == '12')
            {
                $magentoOrder->setStatus('processing_cod');
                $magentoOrder->addStatusHistoryComment('', 'processing_cod')
                    ->setIsVisibleOnFront(true)
                    ->setIsCustomerNotified(true);

                $magentoOrder->save();

                // send email
                $translate = Mage::getSingleton('core/translate');
                $email = Mage::getModel('core/email_template');

                $sender['name'] = Mage::getStoreConfig('trans_email/ident_support/name', Mage::app()->getStore()->getId());
                $sender['email'] = Mage::getStoreConfig('trans_email/ident_support/email', Mage::app()->getStore()->getId());

                $guess = $magentoOrder->getCustomerIsGuest();

                if (!isset ($guess) || $guess == 0) {
                    //login user
                    $customerName = $magentoOrder->getShippingAddress()->getFirstname() . " " . $magentoOrder->getShippingAddress()->getLastname();

                    //must change this id to actual template id
                    $template = Mage::getStoreConfig('bilna_module/cod/template_email_user');
                }
                else {
                    //guest
                    $customerName = "Moms and Dads";

                    //must change this id to actual template id
                    $template = Mage::getStoreConfig('bilna_module/cod/template_email_guest');
                }

                $customerEmail = $magentoOrder->getPayment()->getOrder()->getCustomerEmail();

                $vars = array ('order' => $magentoOrder);
                $storeId = Mage::app()->getStore()->getId();
                $translate = Mage::getSingleton('core/translate');
                Mage::getModel('core/email_template')->sendTransactional($template, $sender, $customerEmail, $customerName, $vars, $storeId);
                $translate->setTranslateInline(true);

                return true;
            }
            else
            if ($magentoOrder->hasInvoices() <= 0)
            {
                // create invoice
                $invoiceMap = array();
                $itemQty = array();

                $request_order_create_invoice_log_file = 'request_order_create_invoice.log';
                
                foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
                    $itemQty[$magentoOrderItem->getId()] = $magentoOrderItem->getQtyOrdered();
                }
                
                /**
                 * Check shipment create availability
                 */
                if (!$magentoOrder->canInvoice()) {
                    Mage::log(($magentoOrder->getNetsuiteInternalId() . ' cannot create invoice'), null, $request_order_create_invoice_log_file);
                   throw new Exception("{$magentoOrder->getId()}: Cannot do shipment for this order!");
                }

                /**
                 * check registry skip invoice export
                 */
                if (!Mage::registry('skip_invoice_export_queue_push')) {
                    Mage::register('skip_invoice_export_queue_push', 1);
                }
                
                //Mage::register('skip_invoice_export_queue_push', 1);
                $magentoInvoice = $magentoOrder->prepareInvoice($itemQty);
                
                if ($magentoInvoice) {
                    $grandTotal = $magentoOrder->getGrandTotal();

                    $magentoInvoice->register();
                    $magentoInvoice->addComment("Create Invoice from Netsuite RO#{$roInternalId}");
                    $magentoInvoice->getOrder()->setIsInProcess(true);
                    $magentoInvoice->setGrandTotal($grandTotal);
                    $magentoInvoice->setBaseGrandTotal($grandTotal);
                    $magentoInvoice->getOrder()->setTotalPaid($grandTotal);
                    $magentoInvoice->getOrder()->setBaseTotalPaid($grandTotal);
                    //$magentoInvoice->getOrder()->save();
                    
                    try {
                        $magentoInvoice->sendEmail(true);
                        $magentoInvoice->setEmailSent(true); 
                        $magentoInvoice->setNetsuiteInternalId($roInternalId);
                        $magentoInvoice->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($lastModifiedDate));

                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($magentoInvoice)
                            ->addObject($magentoInvoice->getOrder())
                            ->save();

                        Mage::log(($magentoOrder->getNetsuiteInternalId() . ' successfully created invoice'), null, $request_order_create_invoice_log_file);
                        
                        return true;
                    }
                    catch (Mage_Core_Exception $e) {
                        throw new Exception("{$magentoOrder->getId()}: {$e->getMessage()}");
                    }
                }
            }
        }

        $magentoOrder->save();
    }

    public function getRequestOrder()
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
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/requestorder/sandbox_import_rst_id');
        }
        else
        {
            $ns_rest_host = "https://rest.netsuite.com";
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/requestorder/production_import_rst_id');
        }

        if (!$scriptId)
            return false;

        $importHelper = Mage::helper('rocketweb_netsuite/queue');
        $lastRequestOrderUpdateDate = $importHelper->getLastUpdateAccessDateSpecificEntity($importHelper::NETSUITE_IMPORT_REQUESTORDER_QUEUE);
        $lastRequestOrderUpdateDate = Mage::getModel('core/date')->date('j/n/Y g:i A', strtotime($lastRequestOrderUpdateDate));

        // variables to be posted to proforma
        $vars = array(
            'last_modified_date' => $lastRequestOrderUpdateDate
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

        $request_order_import_log_file = 'request_order_import.log';

        if ($server_output !== false)
        {
            $server_output = json_decode($server_output, true);
            if ($server_output['status'] == 'success')
            {
                // log the internal ids into log file
                foreach($server_output['result'] as $record)
                    Mage::log($record['internalid'], null, $request_order_import_log_file);
                return $server_output['result'];
            }
            else
            if ($server_output['status'] == 'error')
            {
                Mage::log($server_output['msg'], null, $request_order_import_log_file);
                return $server_output;
            }
        }
        else
        {
            $return['status'] = 'error';
            $return['msg'] = 'Cannot process search Request Order because of calling CURL error';
            Mage::log($return['msg'], null, $request_order_import_log_file);
            return $return;
        }
    }
    
}