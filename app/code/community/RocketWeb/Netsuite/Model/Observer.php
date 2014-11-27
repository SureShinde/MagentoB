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
class RocketWeb_Netsuite_Model_Observer {

    /**
     * @param $observer
     * @return $this
     */
    public function queueCustomerForNetsuite($observer) {
		
		if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
			return $this;
		}

		$customer = $observer->getEvent()->getCustomer();
		
		//prevent executing more than once per event. A customer with 2 addresses that is saved in the admin will trigger the customer_save_after event 3 times
		if(Mage::registry('netsuite_customer_save_'.$customer->getId())) {
			return $this;
		}
		
		Mage::register('netsuite_customer_save_'.$customer->getId(),true);
		
		$message = Mage::getModel('rocketweb_netsuite/queue_message');
		$message->create(RocketWeb_Netsuite_Model_Queue_Message::CUSTOMER_SAVE,$customer->getId(),RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
		Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE)->send($message->pack());
		
		return $this;
	}

    /**
     * @param $observer
     * @return $this
     */
    public function queueCustomerForNetsuiteBasedOnAddress($observer) {

		if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
			return $this;
		}
		
		$customer = $observer->getEvent()->getCustomerAddress()->getCustomer();
		
		//prevent executing more than once per event. A customer with 2 addresses that is saved in the admin will tigger the customer_save_after event 3 times
		if(Mage::registry('netsuite_customer_save_'.$customer->getId())) {
			return $this;
		}
		
		Mage::register('netsuite_customer_save_'.$customer->getId(),true);
		
		$message = Mage::getModel('rocketweb_netsuite/queue_message');
		$message->create(RocketWeb_Netsuite_Model_Queue_Message::CUSTOMER_SAVE,$customer->getId(),RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
		Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE)->send($message->pack());
		
		return $this;
	}

    /**
     * @param $observer
     * @return $this
     */
    public function cleanApiLog($observer) {

		$cleanTime = $observer->getEvent()->getLog()->getLogCleanTime();
		
		$timeLimit = date('Y-m-d H:i:s',(int)Mage::getModel('core/date')->gmtTimestamp() - $cleanTime);
		$writeAdapter   = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		while (true) {
			$select = $writeAdapter->select()->from(array('log_table' => Mage::getSingleton('core/resource')->getTableName('netsuite_api_log')),
													array('id' => 'log_table.id')
												)
												->where('log_table.call_date < ?', $timeLimit)
												->limit(100);
			
			$ids = $writeAdapter->fetchCol($select);	
			if (!$ids) {
				break;
			}
			$condition = array('id IN (?)' => $ids);
			
			$writeAdapter->delete(Mage::getSingleton('core/resource')->getTableName('netsuite_api_log'), $condition);
		}
		
		return $this;
		
	}

    /**
     * @param $observer
     * @return $this
     */
    public function queueCustomerDelete($observer) {

		if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
			return $this;
		}
		$customer = $observer->getEvent()->getCustomer();
		
		$message = Mage::getModel('rocketweb_netsuite/queue_message');
		$message->create(RocketWeb_Netsuite_Model_Queue_Message::CUSTOMER_DELETE,$customer->getId(),RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
		Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE)->send($message->pack());
		
		return $this;
	}

    public function deleteCustomerFromExportQueue($observer) {
        if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return $this;
        }
        $customer = $observer->getEvent()->getCustomer();
        $message = Mage::getModel('rocketweb_netsuite/queue_message')->loadByBody('customer_save|'.$customer->getId());
        while($message && $message->getMessageId()) {
            $message->delete();
            $message = Mage::getModel('rocketweb_netsuite/queue_message')->loadByBody('customer_save|'.$customer->getId());
        }
    }

    /**
     * @return $this
     */
    public function sendWarnings() {

		if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
			return $this;
		}
		
		$devEmail = Mage::getStoreConfig('rocketweb_netsuite/developer/email');
		if(!$devEmail) {
			return $this;
		}
		
		$exportThreshold = (int)Mage::getStoreConfig('rocketweb_netsuite/developer/export_queue_threshold');
		if($exportThreshold) {
			$exportQueueSize = Mage::getModel('rocketweb_netsuite/queue_message')->getCollection()->addFieldToFilter('queue_id',Mage::helper('rocketweb_netsuite/queue')->getQueueId(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE))->getSize();
			if($exportThreshold < $exportQueueSize) {
				$mail = Mage::getModel('core/email');
				$mail->setType('text');
				$mail->setToEmail($devEmail);
				$mail->setToName($devEmail);
				$mail->setFromEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
				$mail->setFromName(Mage::getStoreConfig('trans_email/ident_general/name'));
				$mail->setSubject("Netsuite export queue size threshold reached");
				$mail->setBody("Threshold of $exportThreshold reached ($exportQueueSize elements in the queue)");
				$mail->send();
			}
		}
		
		$importThreshold = (int)Mage::getStoreConfig('rocketweb_netsuite/developer/import_queue_threshold');
		if($importThreshold) {
			$importQueueSize = Mage::getModel('rocketweb_netsuite/queue_message')->getCollection()->addFieldToFilter('queue_id',Mage::helper('rocketweb_netsuite/queue')->getQueueId(RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE))->getSize();
			if($exportThreshold < $importQueueSize) {
				$mail = Mage::getModel('core/email');
				$mail->setType('text');
				$mail->setToEmail($devEmail);
				$mail->setToName($devEmail);
				$mail->setFromEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
				$mail->setFromName(Mage::getStoreConfig('trans_email/ident_general/name'));
				$mail->setSubject("Netsuite import queue size threshold reached");
				$mail->setBody("Threshold of $importThreshold reached ($importQueueSize elements in the queue)");
				$mail->send();
			}
		}
		
		return $this;
	}

    /**
     * Sends new order to netsuite
     * @param $observer
     * @return $this
     */
    public function queueOrderPlaceForNetsuite($observer) {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return $this;
        }
        
        if (!Mage::helper('rocketweb_netsuite/permissions')->isFeatureEnabled(RocketWeb_Netsuite_Helper_Permissions::SEND_ORDERS)) {
            return $this;
        }
		
        $order = $observer->getEvent()->getOrder();
        
        if ($this->checkQueueOrderPlace($order, $observer)) {
            $message = Mage::getModel('rocketweb_netsuite/queue_message');
            $message->create(RocketWeb_Netsuite_Model_Queue_Message::ORDER_PLACE, $order->getId(), RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
            Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE)->send($message->pack());
        }
        
        return $this;
    }
    
    protected function checkQueueOrderPlace($order, $observer) {
        $paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode();
        $paymentMethodCheck = explode(',', Mage::getStoreConfig('rocketweb_netsuite/exports/order_payment_check'));
        
        if (in_array($paymentMethodCode, $paymentMethodCheck)) {
            $orderStatusAllow = explode(',', Mage::getStoreConfig('rocketweb_netsuite/exports/order_status_allow'));
            $orderStatus = $order->getStatus();
            
            if (in_array($orderStatus, $orderStatusAllow)) {
                return true;
            }
            
            return false;
        }
        
        return true;
    }


    /**
     * @param $observer
     * @return $this
     */
    public function queueInvoiceSaveForNetsuite($observer) {
        if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return $this;
        }
        if(!Mage::helper('rocketweb_netsuite/permissions')->isFeatureEnabled(RocketWeb_Netsuite_Helper_Permissions::SEND_INVOICES)) {
            return $this;
        }
        if(Mage::registry('skip_invoice_export_queue_push')) {
            return $this;
        }

        $invoice = $observer->getEvent()->getInvoice();
        $message = Mage::getModel('rocketweb_netsuite/queue_message');
        $message->create(RocketWeb_Netsuite_Model_Queue_Message::INVOICE_SAVE,$invoice->getId(),RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
        Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE)->send($message->pack());

        return $this;
    }

    /**
     * @param $observer
     * @return $this
     */
    public function queueProductSaveForNetsuite($observer) {
        //Net Suite orchestrates product saving
        return $this;


		if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
			return $this;
		}
		
		$product = $observer->getEvent()->getProduct();
		
		$message = Mage::getModel('rocketweb_netsuite/queue_message');
		$message->create(RocketWeb_Netsuite_Model_Queue_Message::PRODUCT_SAVE,$product->getId(),RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
		Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE)->send($message->pack());
		
		return $this;
	}
        
    /**
     * @param type $observer
     * @return \RocketWeb_Netsuite_Model_Observer
     */
    public function queueProductSaveCostNetsuite($observer) {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return $this;
        }
        
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        
        // original value
        $origCost = $product->getOrigData('cost');
        $origExpectedCost = $product->getOrigData('expected_cost');
        $origEventCost = $product->getOrigData('event_cost');
        $origEventStartDate = date('Y-m-d', strtotime($product->getOrigData('event_start_date')));
        $origEventEndDate = date('Y-m-d', strtotime($product->getOrigData('event_end_date')));
        
        // new value
        $newCost = $product->getData('cost');
        $newExpectedCost = $product->getData('expected_cost');
        $newEventCost = $product->getData('event_cost');
        $newEventStartDate = date('Y-m-d', strtotime($product->getData('event_start_date')));
        $newEventEndDate = date('Y-m-d', strtotime($product->getData('event_end_date')));
        
        if (($origCost == $newCost) && ($origExpectedCost == $newExpectedCost) && ($origEventCost == $newEventCost) && ($origEventStartDate == $newEventStartDate) && ($origEventEndDate == $newEventEndDate)) {
            return $this;
        }
        else {
            $model = Mage::getModel('rocketweb_netsuite/productcost');
            $productCost = $model->getCollection()->addFieldToFilter('product_id', $productId)->addFieldToSelect('id')->addFieldToSelect('product_id')->getFirstItem();
            
            // if productId exist, just update row
            if ($productCost->getProductId()) {
                $id = $productCost->getId();
                $dataArr = array (
                    'cost' => $newCost,
                    'expected_cost' => $newExpectedCost,
                    'event_cost' => $newEventCost,
                    'event_start_date' => $newEventStartDate,
                    'event_end_date' => $newEventEndDate
                );
                $update = $model->load($id)->addData($dataArr);
                
                if (!$update->setId($id)->save()) {
                    Mage::log("update queueProductSaveCostNetsuite #" . $product->getId() . " : failed, " . $e->getMessage(), null, "netsuite_product_cost.log");
                }
            }
            else {
                $dataArr = array (
                    'product_id' => $productId,
                    'cost' => $newCost,
                    'expected_cost' => $newExpectedCost,
                    'event_cost' => $newEventCost,
                    'event_start_date' => $newEventStartDate,
                    'event_end_date' => $newEventEndDate,
                    'netsuite_internal_id' => $product->getNetsuiteInternalId()
                );
                $insert = $model->setData($dataArr);

                if (!$insertId = $insert->save()->getId()) {
                    Mage::log("insert queueProductSaveCostNetsuite #" . $product->getId() . " : failed", null, "netsuite_product_cost.log");
                }
            }
        }
                
        return $this;
    }

    public function processStockImport($logger=null) {
        //The following 2 if conditions are redundant, Mage::helper('rocketweb_netsuite/stock')->shouldRun cheks for them too. Keeping them here so we save the
        //getServerTime api call when this feature is disabled
        if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return $this;
        }
        if(!Mage::helper('rocketweb_netsuite/permissions')->isFeatureEnabled(RocketWeb_Netsuite_Helper_Permissions::GET_STOCK_UPDATES)) {
            return $this;
        }

        $currentDate = Mage::helper('rocketweb_netsuite')->getServerTime();

        if(Mage::helper('rocketweb_netsuite/stock')->shouldRun(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($currentDate))) {
            Mage::helper('rocketweb_netsuite/stock')->processStockUpdates($logger);
            Mage::helper('rocketweb_netsuite/stock')->setLastUpdateDate($currentDate);
        }

        return $this;
    }

    public function cleanApiCallLog() {
        if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return $this;
        }
        if(!Mage::getStoreConfig('rocketweb_netsuite/developer/enabled')) {
            return $this;
        }

        $cleanAfterDays = (int) Mage::getStoreConfig('rocketweb_netsuite/developer/clean_api_call_log_after');
        if(!$cleanAfterDays) {
            return $this;
        }
        $tableName = Mage::getSingleton('core/resource')->getTableName('netsuite_api_log');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "DELETE FROM $tableName WHERE DATE_ADD(call_date,INTERVAL $cleanAfterDays DAY) < NOW()";
        $connection->query($query);

        return $this;
    }

    public function cleanChangelog() {
        if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return $this;
        }
        if(!Mage::getStoreConfig('rocketweb_netsuite/developer/changelog_enabled')) {
            return $this;
        }

        $cleanAfterDays = (int) Mage::getStoreConfig('rocketweb_netsuite/developer/changelog_lifetime');
        if(!$cleanAfterDays) {
            return $this;
        }
        $tableName = Mage::getSingleton('core/resource')->getTableName('netsuite_changelog');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "DELETE FROM $tableName WHERE DATE_ADD(created_date,INTERVAL $cleanAfterDays DAY) < NOW()";
        $connection->query($query);

        return $this;
    }

}