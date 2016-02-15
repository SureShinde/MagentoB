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
class RocketWeb_Netsuite_Helper_Queue extends Mage_Core_Helper_Data {
	const NETSUITE_IMPORT_QUEUE = 'netsuite_import';
	const NETSUITE_EXPORT_QUEUE = 'netsuite_export';
    const NETSUITE_DELETE_QUEUE = 'netsuite_delete';

    const NETSUITE_IMPORT_ORDER_QUEUE = 'netsuite_import_order';
    const NETSUITE_IMPORT_ORDER_FULFILLMENT_QUEUE = 'netsuite_import_order_fulfillment';
    const NETSUITE_IMPORT_INVOICE_QUEUE = 'netsuite_import_invoice';
    const NETSUITE_IMPORT_INVENTORYITEM_QUEUE = 'netsuite_import_inventoryitem';
    const NETSUITE_IMPORT_CASHSALE_QUEUE = 'netsuite_import_cashsale';
    const NETSUITE_IMPORT_CREDITMEMO_QUEUE = 'netsuite_import_creditmemo';

    const NETSUITE_DELETE_ORDER_QUEUE = 'netsuite_delete_order';
    const NETSUITE_DELETE_ORDER_FULFILLMENT_QUEUE = 'netsuite_delete_order_fulfillment';
    const NETSUITE_DELETE_INVOICE_QUEUE = 'netsuite_delete_invoice';
    const NETSUITE_DELETE_INVENTORYITEM_QUEUE = 'netsuite_delete_inventoryitem';
    const NETSUITE_DELETE_CASHSALE_QUEUE = 'netsuite_delete_cashsale';
    const NETSUITE_DELETE_CREDITMEMO_QUEUE = 'netsuite_delete_creditmemo';
	
	protected $_queues = array();
	protected $_queue_ids = array();
	
	public function getQueueId($queueName) {
		if(!isset($this->_queue_ids[$queueName])) {
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$connection->closeConnection();
			$connection->getConnection();
			$sql = $connection->quoteInto("SELECT queue_id FROM ".Mage::getSingleton('core/resource')->getTableName('queue')." WHERE queue_name=?",$queueName);
			$id = $connection->fetchOne($sql);
			$this->_queue_ids[$queueName] = $id;
		}
		return $this->_queue_ids[$queueName];
	}

    public function messageExistsInQueue(RocketWeb_Netsuite_Model_Queue_Message $message) {
        $identifier = $message->getUniqueIdentifier();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $connection->closeConnection();
		$connection->getConnection();
        $sql = $connection->quoteInto("SELECT COUNT(*) FROM ".Mage::getSingleton('core/resource')->getTableName('message')." WHERE body LIKE ?",$identifier.'|%');
        if($connection->fetchOne($sql)) {
            return true;
        }
        else return false;
    }
	
	public function getQueue($queueName) {

		if(!in_array($queueName, array(self::NETSUITE_EXPORT_QUEUE,self::NETSUITE_IMPORT_QUEUE,self::NETSUITE_DELETE_QUEUE))) {
			throw new Exception("Queue name must be ".self::NETSUITE_IMPORT_QUEUE." , ".self::NETSUITE_EXPORT_QUEUE.' or '.self::NETSUITE_DELETE_QUEUE);
		}

		if(!isset($this->_queues[$queueName])) {
			$dbConfig = Mage::getConfig()->getResourceConnectionConfig("default_setup");
			$queueOptions = array(
					Zend_Queue::NAME => $queueName,
					Zend_Queue::TIMEOUT => Mage::getStoreConfig('rocketweb_netsuite/queue_processing/timeout'),
					Zend_Queue::VISIBILITY_TIMEOUT => Mage::getStoreConfig('rocketweb_netsuite/queue_processing/timeout'),
                    'adapterNamespace' => 'RocketWeb_Netsuite_Model_Queue_Adapter',
					'driverOptions' => array(
						'host' => $dbConfig->host,
						'port' => $dbConfig->port,
						'username' => $dbConfig->username,
						'password' => $dbConfig->password,
						'dbname' => $dbConfig->dbname,
						'type' => 'pdo_mysql',
					)
			);
			$this->_queues[$queueName] = new RocketWeb_Netsuite_Model_Queue('Db', $queueOptions);
		}

		return $this->_queues[$queueName];
	}

    public function setLastUpdateAccessDate($netsuiteDateString,$queueType) {
        $netsuiteDateString = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($netsuiteDateString);
        if(!in_array($queueType,array(self::NETSUITE_IMPORT_QUEUE,self::NETSUITE_DELETE_QUEUE))) {
            throw new Exception("Queue type must be ".self::NETSUITE_IMPORT_QUEUE." or ".self::NETSUITE_DELETE_QUEUE);
        }
        if($queueType == self::NETSUITE_IMPORT_QUEUE) {
            $variableName = 'last_import_queue_run_date';
        }
        if($queueType == self::NETSUITE_DELETE_QUEUE) {
            $variableName = 'last_delete_queue_run_date';
        }
        $flagModel = Mage::getModel('core/flag',array('flag_code'=>$variableName))->loadSelf();
        $flagModel->setFlagData($netsuiteDateString);
        $flagModel->save();
    }

    public function setLastUpdateAccessDateSpecificEntity($netsuiteDateString,$importEntity) {
        $netsuiteDateString = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($netsuiteDateString);
        
        if(!in_array($importEntity,array(self::NETSUITE_IMPORT_ORDER_QUEUE, self::NETSUITE_IMPORT_ORDER_FULFILLMENT_QUEUE, self::NETSUITE_IMPORT_INVOICE_QUEUE, self::NETSUITE_IMPORT_INVENTORYITEM_QUEUE, self::NETSUITE_IMPORT_CASHSALE_QUEUE, self::NETSUITE_IMPORT_CREDITMEMO_QUEUE, self::NETSUITE_DELETE_ORDER_QUEUE, self::NETSUITE_DELETE_ORDER_FULFILLMENT_QUEUE, self::NETSUITE_DELETE_INVOICE_QUEUE, self::NETSUITE_DELETE_INVENTORYITEM_QUEUE, self::NETSUITE_DELETE_CASHSALE_QUEUE, self::NETSUITE_DELETE_CREDITMEMO_QUEUE))) {
            throw new Exception("Queue import entity must be ".self::NETSUITE_IMPORT_ORDER_QUEUE.", ".self::NETSUITE_IMPORT_ORDER_FULFILLMENT_QUEUE.", ".self::NETSUITE_IMPORT_INVENTORYITEM_QUEUE.", ".self::NETSUITE_IMPORT_CASHSALE_QUEUE.", ".self::NETSUITE_IMPORT_CREDITMEMO_QUEUE.", ".self::NETSUITE_DELETE_ORDER_QUEUE.", ".self::NETSUITE_DELETE_ORDER_FULFILLMENT_QUEUE.", ".self::NETSUITE_DELETE_INVOICE_QUEUE.", ".self::NETSUITE_DELETE_INVENTORYITEM_QUEUE.", ".self::NETSUITE_DELETE_CASHSALE_QUEUE." or ".self::NETSUITE_DELETE_CREDITMEMO_QUEUE);
        }

        switch ($importEntity) {
		    case self::NETSUITE_IMPORT_ORDER_QUEUE:
		        $variableName = 'last_import_order_queue_run_date';
		        break;
		    case self::NETSUITE_IMPORT_ORDER_FULFILLMENT_QUEUE:
		        $variableName = 'last_import_order_fulfillment_queue_run_date';
		        break;
		    case self::NETSUITE_IMPORT_INVENTORYITEM_QUEUE:
		        $variableName = 'last_import_inventoryitem_queue_run_date';
		        break;
			case self::NETSUITE_IMPORT_INVOICE_QUEUE:
		        $variableName = 'last_import_invoice_queue_run_date';
		        break;
		    case self::NETSUITE_IMPORT_CASHSALE_QUEUE:
		        $variableName = 'last_import_cashsale_queue_run_date';
		        break;
		    case self::NETSUITE_IMPORT_CREDITMEMO_QUEUE:
		        $variableName = 'last_import_creditmemo_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_ORDER_QUEUE:
		        $variableName = 'last_delete_order_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_ORDER_FULFILLMENT_QUEUE:
		        $variableName = 'last_delete_order_fulfillment_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_INVENTORYITEM_QUEUE:
		        $variableName = 'last_delete_inventoryitem_queue_run_date';
		        break;
			case self::NETSUITE_DELETE_INVOICE_QUEUE:
		        $variableName = 'last_delete_invoice_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_CASHSALE_QUEUE:
		        $variableName = 'last_delete_cashsale_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_CREDITMEMO_QUEUE:
		        $variableName = 'last_delete_creditmemo_queue_run_date';
		        break;
		    default:
		        $variableName = 'last_import_queue_run_date';
		}

        $flagModel = Mage::getModel('core/flag',array('flag_code'=>$variableName))->loadSelf();
        if (!$flagModel->getFlagData())
        	$flagModel->setFlagData($netsuiteDateString);
        else
        	$flagModel->setFlagData($netsuiteDateString);

        $flagModel->save();
    }

    public function getLastUpdateAccessDate($queueType) {
        if(!in_array($queueType,array(self::NETSUITE_IMPORT_QUEUE,self::NETSUITE_DELETE_QUEUE))) {
            throw new Exception("Queue type mus be ".self::NETSUITE_IMPORT_QUEUE." or ".self::NETSUITE_DELETE_QUEUE);
        }
        if($queueType == self::NETSUITE_IMPORT_QUEUE) {
            $variableName = 'last_import_queue_run_date';
        }
        if($queueType == self::NETSUITE_DELETE_QUEUE) {
            $variableName = 'last_delete_queue_run_date';
        }
        $flagModel = Mage::getModel('core/flag',array('flag_code'=>$variableName))->loadSelf();
        return $flagModel->getFlagData();
    }

    public function getLastUpdateAccessDateSpecificEntity($importEntity) {
    	if(!in_array($importEntity,array(self::NETSUITE_IMPORT_ORDER_QUEUE, self::NETSUITE_IMPORT_ORDER_FULFILLMENT_QUEUE, self::NETSUITE_IMPORT_INVOICE_QUEUE, self::NETSUITE_IMPORT_INVENTORYITEM_QUEUE, self::NETSUITE_IMPORT_CASHSALE_QUEUE, self::NETSUITE_IMPORT_CREDITMEMO_QUEUE, self::NETSUITE_DELETE_ORDER_QUEUE, self::NETSUITE_DELETE_ORDER_FULFILLMENT_QUEUE, self::NETSUITE_DELETE_INVOICE_QUEUE, self::NETSUITE_DELETE_INVENTORYITEM_QUEUE, self::NETSUITE_DELETE_CASHSALE_QUEUE, self::NETSUITE_DELETE_CREDITMEMO_QUEUE))) {
            throw new Exception("Queue import entity must be ".self::NETSUITE_IMPORT_ORDER_QUEUE.", ".self::NETSUITE_IMPORT_ORDER_FULFILLMENT_QUEUE.", ".self::NETSUITE_IMPORT_INVENTORYITEM_QUEUE.", ".self::NETSUITE_IMPORT_CASHSALE_QUEUE.", ".self::NETSUITE_IMPORT_CREDITMEMO_QUEUE.", ".self::NETSUITE_DELETE_ORDER_QUEUE.", ".self::NETSUITE_DELETE_ORDER_FULFILLMENT_QUEUE.", ".self::NETSUITE_DELETE_INVOICE_QUEUE.", ".self::NETSUITE_DELETE_INVENTORYITEM_QUEUE.", ".self::NETSUITE_DELETE_CASHSALE_QUEUE." or ".self::NETSUITE_DELETE_CREDITMEMO_QUEUE);
        }

        switch ($importEntity) {
		    case self::NETSUITE_IMPORT_ORDER_QUEUE:
		        $variableName = 'last_import_order_queue_run_date';
		        break;
		    case self::NETSUITE_IMPORT_ORDER_FULFILLMENT_QUEUE:
		        $variableName = 'last_import_order_fulfillment_queue_run_date';
		        break;
		    case self::NETSUITE_IMPORT_INVENTORYITEM_QUEUE:
		        $variableName = 'last_import_inventoryitem_queue_run_date';
		        break;
			case self::NETSUITE_IMPORT_INVOICE_QUEUE:
		        $variableName = 'last_import_invoice_queue_run_date';
		        break;
		    case self::NETSUITE_IMPORT_CASHSALE_QUEUE:
		        $variableName = 'last_import_cashsale_queue_run_date';
		        break;
		    case self::NETSUITE_IMPORT_CREDITMEMO_QUEUE:
		        $variableName = 'last_import_creditmemo_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_ORDER_QUEUE:
		        $variableName = 'last_delete_order_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_ORDER_FULFILLMENT_QUEUE:
		        $variableName = 'last_delete_order_fulfillment_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_INVENTORYITEM_QUEUE:
		        $variableName = 'last_delete_inventoryitem_queue_run_date';
		        break;
			case self::NETSUITE_DELETE_INVOICE_QUEUE:
		        $variableName = 'last_delete_invoice_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_CASHSALE_QUEUE:
		        $variableName = 'last_delete_cashsale_queue_run_date';
		        break;
		    case self::NETSUITE_DELETE_CREDITMEMO_QUEUE:
		        $variableName = 'last_delete_creditmemo_queue_run_date';
		        break;
		    default:
		        $variableName = 'last_import_queue_run_date';
		}

        $flagModel = Mage::getModel('core/flag',array('flag_code'=>$variableName))->loadSelf();
        if (!$flagModel->getFlagData())
        {
        	if(strpos($variableName, 'import') !== false)
        		$defaultVariableName = 'last_import_queue_run_date';
        	else
        	if(strpos($variableName, 'delete') !== false)
        		$defaultVariableName = 'last_delete_queue_run_date';

        	$flagModel = Mage::getModel('core/flag',array('flag_code'=>$defaultVariableName))->loadSelf();
        }

        return $flagModel->getFlagData();
    }

}