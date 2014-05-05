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
class RocketWeb_Netsuite_Model_Queue_Message  extends Mage_Core_Model_Abstract {

	const CUSTOMER_SAVE     = 'customer_save';
	const CUSTOMER_DELETE   = 'customer_delete';
	const ORDER_PLACE       = 'order_place';
	const PRODUCT_SAVE      = 'product_save';
    const INVOICE_SAVE      = 'invoice_save';
    const FULFILLMENT_IMPORTED = 'order_fulfillment';
    const CASHSALE_IMPORTED = 'cashsale';
    const ORDER_IMPORTED = 'order';
    const INVENTORY_UPDATED = 'inventory';
    const FULFILLMENT_DELETED = 'order_fulfillment_delete';
    const CASHSALE_DELETED = 'cashsale_delete';
    const ORDER_DELETED = 'order_delete';
    const INVENTORY_DELETED = 'inventory_delete';
    const PRODUCT_UPDATED = 'inventoryitem';
    const PRODUCT_DELETED = 'inventoryitem_delete';

	protected $id = null;
	protected $action = null;
    protected $object = null;
	
	public function getAction() {
		return $this->action;
	}
	
	public function getEntityId() {
		return $this->id;
	}

    public function getObject() {
        return $this->object;
    }
	
	public function _construct() {

		parent::_construct();
		$this->_init('rocketweb_netsuite/queue_message');
	}

    /**
     * @param $action
     * @param $id
     * @param $queueName
     * @return $this
     * @throws Exception
     */
    public function create($action,$id,$queueName,$serializableObject = null) {

		if(!in_array($queueName,array(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE,RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE,RocketWeb_Netsuite_Helper_Queue::NETSUITE_DELETE_QUEUE))) {
            throw new Exception("Queue name must be ".self::NETSUITE_IMPORT_QUEUE." , ".self::NETSUITE_EXPORT_QUEUE.' or '.self::NETSUITE_DELETE_QUEUE);
		}
		if(!in_array($action,$this->getValidActions($queueName))) {
			throw new Exception("Invalid action type!");
		}
		$this->id = $id;
		$this->action = $action;
        if($serializableObject) {
            $this->object = $serializableObject;
        }

		return $this;
	}

    /**
     * @return string
     * @throws Exception
     */
    public function pack() {

		if(!isset($this->action) || !isset($this->id)) {
			throw new Exception("Message not initialized");
		}
		$str = $this->action.'|'.$this->id;
        if($this->object) {
            $str.='|'.serialize($this->object);
        }
        return $str;
	}

    /**
     * @return string
     * @throws Exception
     */
    public function getUniqueIdentifier() {
        if(!isset($this->action) || !isset($this->id)) {
            throw new Exception("Message not initialized");
        }
        return $this->action.'|'.$this->id;
    }

    /**
     * @param $queueName
     * @return array
     */
    protected function getValidActions($queueName) {

		if($queueName == RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE) {
			return array(self::CUSTOMER_SAVE,self::CUSTOMER_DELETE,self::ORDER_PLACE,self::PRODUCT_SAVE,self::INVOICE_SAVE);
		}
		if($queueName == RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE) {
			return array(self::FULFILLMENT_IMPORTED,self::CASHSALE_IMPORTED,self::ORDER_IMPORTED,self::INVENTORY_UPDATED,self::PRODUCT_UPDATED);
		}
        if($queueName == RocketWeb_Netsuite_Helper_Queue::NETSUITE_DELETE_QUEUE) {
            return array(self::FULFILLMENT_DELETED,self::CASHSALE_DELETED,self::ORDER_DELETED,self::INVENTORY_DELETED,self::PRODUCT_DELETED);
        }
		return array();
	}

    public function getQueueType() {
        $parts = explode('|',$this->getBody());
        $type = $parts[0];
        if(in_array($type,$this->getValidActions(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE))) {
            return RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE;
        }
        if(in_array($type,$this->getValidActions(RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE))) {
            return RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE;
        }
        if(in_array($type,$this->getValidActions(RocketWeb_Netsuite_Helper_Queue::FULFILLMENT_DELETED))) {
            return RocketWeb_Netsuite_Helper_Queue::FULFILLMENT_DELETED;
        }
        return null;
    }

    /**
     * @param $string
     * @param $queueName
     * @return RocketWeb_Netsuite_Model_Queue_Message
     */
    static public function unpack($string,$queueName) {

		$elements = explode('|', $string,3);
		$message = new RocketWeb_Netsuite_Model_Queue_Message();
        if(isset($elements[2])) {

            $message->create($elements[0],$elements[1],$queueName,unserialize($elements[2]));
        }
        else {
		    $message->create($elements[0],$elements[1],$queueName);
        }

		return $message;
	}

    public function loadByBody($body) {
        $this->_getResource()->loadByBody($this, $body);
        return $this;
    }
}