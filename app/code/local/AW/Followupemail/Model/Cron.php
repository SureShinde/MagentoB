<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Followupemail
 * @version    3.5.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */
class AW_Followupemail_Model_Cron {
    /**
     * Enable debug mode for extended cron events logging
     */

    const DEBUG_MODE = FALSE;

    /**
     * Session timeout after which abandoned cart event may trigger
     */
    //const SESSION_TIMEOUT = 3600; // 1 hour
    const SESSION_TIMEOUT = 0;

    /**
     * Count of items from orders history collection that will be processed at one pass
     * Also used in check new customers function
     * Miximum value of this constant depends on RAM capacity in your server.
     */
    const ITEMS_PER_ONCE = 50;

    /**
     * ID of cache record with FUE lock
     */
    const CACHE_LOCK_ID = 'aw_hdu_lock';

    /*
     * Cron run interval (in seconds)
     */
    const LOCK_EXPIRE_INTERVAL = 1800; // 30 minutes

    /*
     * @var int Last execution time
     */

    protected $_lastExecTime = false;

    /*
     * @var string Last execution time string representation in MySQL datetime format
     */
    protected $_lastExecTimeMySQL = false;

    /*
     * @var int Time of job start
     */
    protected $_now = false;

    /*
     * @var string Time of job started in MySQL datetime format
     */
    protected $_nowMySQL = false;

    /*
     * Constructor
     */

    public function __construct() {
        clearstatcache();
    }

    /**
     * Checks if one FUE is already running
     * @return
     */
    public static function checkLock() {
        if (($time = Mage::app()->loadCache(self::CACHE_LOCK_ID))) {
            if ((time() - $time) > self::LOCK_EXPIRE_INTERVAL) {
                // Old expired lock
            } else {
                return false;
            }
        }
        Mage::app()->saveCache(time(), self::CACHE_LOCK_ID, array(), self::LOCK_EXPIRE_INTERVAL);
        return true;
    }

    /*
     * Checks events
     */

    protected function _checkEvents() {
        //echo "---check event";
        $this->_checkOrderStatusHistory();
		$this->_checkPaymentMethod();
        $this->_checkAbandonedCarts();
        $this->_checkCustomerActivity();
    }

    protected function _checkCustomerActivity() {
        $this->_checkCustomerLogin();
        $this->_checkCustomerLastActivity();
    }

    /*
     * Runs cron job
     */

    public function cronJobs() {
        //echo "---cronjob start";
        $config = Mage::getModel('followupemail/config');

        if (!$this->_lastExecTime = $config->getParam(AW_Followupemail_Model_Config::LAST_EXEC_TIME)) {
            $config->setParam(AW_Followupemail_Model_Config::LAST_EXEC_TIME, time());
            if (!self::DEBUG_MODE)
                return;
        }

        $this->_now = time();

        if (!self::checkLock()) {
            AW_Followupemail_Model_Log::log('FUE is already running');
            if (!self::DEBUG_MODE)
                return;
        }

        AW_Followupemail_Model_Sender::sendPrepared();

        $this->_nowMySQL = date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_now);
        $this->_lastExecTimeMySQL = date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_lastExecTime);

        /* add GMT+7 */
        /*
        $this->_nowMySQL = date('Y-m-d H:i:s', strtotime('+7 hours', strtotime($this->_nowMySQL)));
        $this->_lastExecTimeMySQL = date('Y-m-d H:i:s', strtotime('+7 hours', strtotime($this->_lastExecTimeMySQL)));
        */

        try {
            $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);

            AW_Followupemail_Model_Log::log('cron started, last execution time is '
                    . date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_lastExecTime + $timeShift));

            //echo "---remove old coupons";
            $this->_removeOldCoupons();
            //echo "---end remove old coupons";
            $this->_checkEvents();
            $config->setParam(AW_Followupemail_Model_Config::LAST_EXEC_TIME, $this->_now);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        Mage::app()->removeCache(self::CACHE_LOCK_ID);
        $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);
        AW_Followupemail_Model_Log::log('cron stopped at ' . date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time() + $timeShift) .
                '. Last time is ' . date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_now + $timeShift));
    }

    /**
     * Removes old coupons, generated by FUE
     */
    protected function _removeOldCoupons() {
        if (!Mage::helper('followupemail/coupon')->canUseCoupons()) {
            return;
        }
        if (self::DEBUG_MODE)
            AW_Followupemail_Model_Log::log('Removing old coupons');
        $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);
        $expires = date(AW_Followupemail_Helper_Coupon::MYSQL_DATETIME_FORMAT, time() + $timeShift);
        $collection = Mage::getModel('salesrule/coupon')->getCollection();
        $collection->getSelect()->joinLeft(array('scr' => $collection->getTable('salesrule/rule')), 'main_table.rule_id = scr.rule_id')
                ->where('scr.coupon_type = ?', Mage::helper('followupemail/coupon')->getFUECouponsCode())
                ->where('expiration_date <= ?', $expires);

        if (self::DEBUG_MODE)
            AW_Followupemail_Model_Log::log(sprintf("Total %d old coupons found", $collection->getSize()));

        $where = $collection->getConnection()->quoteInto('coupon_id IN (?)', $collection->getAllIds());
        $collection->getConnection()->delete($collection->getMainTable(), $where);

        if (self::DEBUG_MODE)
            AW_Followupemail_Model_Log::log('Completed removing old coupons');
    }

    /*
     * Checks order status history
     */

    protected function _checkOrderStatusHistory() {
        if (self::DEBUG_MODE)
            AW_Followupemail_Model_Log::log("Processing Order Status History");
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $config = Mage::getModel('followupemail/config');

        if (($lastId = $config->getParam(AW_Followupemail_Model_Config::ORDER_STATUS_HISTORY_ID)) === false) {
            $config->setParam(AW_Followupemail_Model_Config::ORDER_STATUS_HISTORY_ID, 0);
        }

        $currentPage = 1;
        $_pages = 1;
        while ($currentPage <= $_pages) {
            if (self::DEBUG_MODE)
                AW_Followupemail_Model_Log::log("Processing page {$currentPage} from {$_pages} total");
            /** @var $statusHistoryCollection  Mage_Sales_Model_Entity_Order_Status_History_Collection */
            $statusHistoryCollection = Mage::getModel('sales/order_status_history')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('created_at', array('lt' => $this->_nowMySQL));
            if ($lastId) {
                $statusHistoryCollection->addAttributeToFilter('entity_id', array('gt' => $lastId));
            } else {
                $statusHistoryCollection->addAttributeToFilter('created_at', array('gt' => $this->_lastExecTimeMySQL));				
            }
            $statusHistoryCollection->setPageSize(self::ITEMS_PER_ONCE)
                    ->setCurPage($currentPage)
                    ->load();

            if (!$statusHistoryCollection->getSize())
                return;

            if ($_pages == 1 && ceil($statusHistoryCollection->getSize() / self::ITEMS_PER_ONCE) > 1)
                $_pages = (int) ceil($statusHistoryCollection->getSize() / self::ITEMS_PER_ONCE);

            $order = Mage::getModel('sales/order');
            $queue = Mage::getResourceModel('followupemail/queue');
            $dbReader = Mage::getResourceModel('followupemail/rule');

            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $select = $read->select()
                    ->distinct()
                    ->from(array('qio' => $resource->getTableName('sales/quote_item_option')), '')
                    ->joinInner(array('qi' => $resource->getTableName('sales/quote_item')), 'qi.item_id=qio.item_id', '')
                    ->joinInner(array('q' => $resource->getTableName('sales/quote')), 'q.entity_id=qi.quote_id', '')
                    ->joinInner(array('o' => $resource->getTableName('sales/order')), 'o.increment_id=q.reserved_order_id', '')
                    ->columns('qio.value')
                    ->where('qio.code="product_type"');
            foreach ($statusHistoryCollection->getItems() as $historyItem) {
                $eventName = AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX . $historyItem->getStatus();
				$order->reset()->load($historyItem->getParentId());
                if (($eventName == AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX . 'pending') ||
                        ($eventName == AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX . 'pending_payment')
                )
                $queue->cancelByEvent($order->getCustomerEmail(), AW_Followupemail_Model_Source_Rule_Types::CANCEL_TYPE_CUSTOMER_PLACED_NEW_ORDER, $order->getId());
                $queue->cancelByEvent($order->getCustomerEmail(), $eventName, $order->getId());
                $queue->cancelByEvent($order->getCustomerEmail(), $eventName, $order->getIncrementId());
                $queue->cancelByEvent($order->getCustomerEmail(), $eventName, $order->getQuoteId());

                $ruleIds = $dbReader->getRuleIdsByEventType($eventName);
                foreach ($ruleIds as $key => $value)
                    if ($dbReader->isOrderStatusProcessed($historyItem->getParentId(), $value)) {
                        AW_Followupemail_Model_Log::log('order status duplicated, orderId=' . $historyItem->getParentId() . ' status=' . $historyItem->getStatus() . ' ruleId=' . $value);
                        unset($ruleIds[$key]);
                    }

                if (!empty($ruleIds)) {

                    $productIds = array();
                    $categoryIds = '';
                    $productTypeIds = array();
                    $sku = array();

                    $orderItemProduct = Mage::getModel('catalog/product');
                    $extraInfo = $read->fetchCol($select->where('o.entity_id=?', $order->getId()));

                    foreach ($extraInfo as $productTypeId)
                        $productTypeIds[$productTypeId] = true;

                    foreach ($order->getAllItems() as $orderItem) {
                        $orderItemProduct->unsetData()->load($orderItem->getProductId());

                        $ids = $orderItemProduct->getCategoryIds();
                        if (is_array($ids))
                            $ids = implode(',', $ids);
                        $categoryIds .= ',' . $ids;

                        $productTypeIds[$orderItemProduct->getTypeId()] = true;
                        $sku[] = $orderItem->getSku();
                        $productIds[] = $orderItem->getId();
                    }

                    $params = array();
                    $params['store_id'] = $order->getStoreId();
                    $params['category_ids'] = Mage::helper('followupemail')->noEmptyValues(array_unique(explode(',', $categoryIds)));
                    $params['product_type_ids'] = array_keys($productTypeIds);
                    $params['sku'] = $sku;
                    $params['product_ids'] = $productIds;

                    $customerId = $order->getCustomerId();
                    if ($customerId)
                        $params['customer_id'] = $customerId;
                    else
                        $params['customer_email'] = $order->getCustomerEmail();

                    foreach ($ruleIds as $ruleId) {
                        AW_Followupemail_Model_Log::log("order status orderId={$order->getId()} validating, ruleId=$ruleId");

                        $objects = array();
                        $objects['object_id'] = $order->getId();
                        $objects['order_id'] = $order->getId();
                        $objects['order'] = $order;
                        $objects['customer_is_guest'] = $order->getCustomerIsGuest();

                        Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects); //add
                    }
                }
                $config->setParam(AW_Followupemail_Model_Config::ORDER_STATUS_HISTORY_ID, $historyItem->getId());
            }

            unset($statusHistoryCollection);
            $currentPage++;
        }
    }

    /*
     * Checks payment method
     */
        
    protected function _checkPaymentMethod(){
        $order = Mage::getModel('sales/order');
        $queueresource = Mage::getResourceModel('followupemail/queue');
        $queue = Mage::getModel('followupemail/queue');
        $dbReader = Mage::getResourceModel('followupemail/rule');
        
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        
        $eventName = AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_PAYMENT_METHOD.$payment["method"];     
        
        $query = "select afr.id, afq.object_id, afr.event_type, afr.cancel_events from aw_followup_queue afq, aw_followup_rule afr WHERE afr.id = afq.rule_id AND ( afr.cancel_events = 'order_status_processing' OR afr.cancel_events = 'order_status_processing_cod' OR afr.cancel_events = 'order_status_canceled' ) AND afq.`status` = 'R'";
        $queueList = $read->fetchAll($query);

        foreach($queueList as $queueRow){
            $query = "select so.entity_id, so.status, so.customer_email from sales_flat_order_payment sop inner join sales_flat_order so on sop.parent_id=so.entity_id where sop.parent_id = '".$queueRow["object_id"]."'";
            $order = $read->fetchRow($query);
            
            if ($queueRow["cancel_events"] == "order_status_".$order['status']){                            
                $queueresource->cancelByEvent($order['customer_email'], $queueRow['event_type'], $order['entity_id'], $queueRow["cancel_events"]);          
            }
        }

        $select = $read->select()
            ->from(array('sop' => $resource->getTableName('sales/order_payment')), array('method' => 'sop.method'))
            ->joinInner(array('so' => $resource->getTableName('sales/order')), 'sop.parent_id=so.entity_id')
            ->where("so.created_at between '".$this->_lastExecTimeMySQL."' and '".$this->_nowMySQL."'")
            //->where("so.created_at between '2016-02-03 00:00:00' and '2016-02-05 23:59:59'")
            ->where("so.`status`='pending' or so.`status`='pending_cod'");
        $payments = $read->fetchAll($select);
        $sequenceNumber = 1;
        //echo "<pre>";
        foreach($payments as $payment){
                                    
            $query = "select afr.* FROM aw_followup_rule afr WHERE id = afr.id AND afr.is_active=1 AND (afr.active_to is NULL OR afr.active_to >= NOW()) AND afr.event_type = '".AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_PAYMENT_METHOD.$payment["method"]."'";
            $method = $read->fetchRow($query);

            if ($method != false)
            {
                $fueRule = Mage::getModel('followupemail/rule')->load($method['id']);
                
                foreach (unserialize($fueRule->getChain()) as $chain) {
                    $params = array();
                    $params['payment_method'] = $payment['method'];
                    $params['object_id'] = $payment['entity_id'];
                    $params['order_id'] = $payment['entity_id'];
                    $params['store_id'] = Mage::app()->getStore()->getStoreId();
                    $params['customer_email'] = $payment['customer_email'];
                    $params['customer_id'] = $payment['customer_id'];
                    
                    $templateId = $chain['TEMPLATE_ID'];                    
                    $timeDelay = $chain['DAYS'] * 1440 + $chain['HOURS'] * 60 + $chain['MINUTES'];
                    
                    AW_Followupemail_Model_Log::log('paymentMethod event processing, rule_id=' . $method['id'] . ', customerEmail=' . $params['customer_email'] . ', store_id=' . $params['store_id']);
                    $fueRule->processPaymentMethod($params, $templateId, $timeDelay, $sequenceNumber);
                }
                $sequenceNumber++;
            }
        }
        
        return true;
    }
	
    /*
     * Checks for new abandoned carts appeared
     */

    protected function _checkAbandonedCarts() {
        //echo strtotime(date("Y-m-d H:i:s", strtotime("- ".abs(12)." hours")));
        if (self::DEBUG_MODE)
            AW_Followupemail_Model_Log::log("Checking abandoned carts");
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');

		//check bigger
		$lastExecutimeTime = strtotime(date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_lastExecTime - self::SESSION_TIMEOUT));
		$checkedTime = strtotime(date("Y-m-d H:i:s", strtotime("- 4 days")));
		if($lastExecutimeTime>$checkedTime){
			$exeFromDate = date("Y-m-d H:i:s", strtotime("- 4 days"));		
		}else{
			$exeFromDate = date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_lastExecTime - self::SESSION_TIMEOUT);
		}		
		//Zend_Debug::Dump($exeFromDate); die;
		
        $select = $read->select()
                ->from(array('q' => $resource->getTableName('sales/quote')), array(
                    'store_id' => 'q.store_id',
                    'quote_id' => 'q.entity_id',
                    'customer_id' => 'q.customer_id',
                    'updated_at' => 'q.updated_at'))
                ->joinLeft(array('a' => $resource->getTableName('sales/quote_address')), 'q.entity_id=a.quote_id AND a.address_type="billing"', array(
                    'customer_email' => new Zend_Db_Expr('IFNULL(q.customer_email, a.email)'),
                    'customer_firstname' => new Zend_Db_Expr('IFNULL(q.customer_firstname, a.firstname)'),
                    'customer_middlename' => new Zend_Db_Expr('IFNULL(q.customer_middlename, a.middlename)'),
                    'customer_lastname' => new Zend_Db_Expr('IFNULL(q.customer_lastname, a.lastname)'),
                ))
				->joinLeft(array('awq' => $resource->getTableName('followupemail/queue')), 'q.entity_id=awq.object_id AND awq.status="R"', array(
					'status' => 'awq.status',
				))
                ->joinInner(array('i' => $resource->getTableName('sales/quote_item')), 'q.entity_id=i.quote_id', array(
                    'product_ids' => new Zend_Db_Expr('GROUP_CONCAT(i.product_id)'),
                    'item_ids' => new Zend_Db_Expr('GROUP_CONCAT(i.item_id)')
                ))
                ->where('q.is_active=1')
                //->where('q.updated_at > ?', date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_lastExecTime - self::SESSION_TIMEOUT))
				->where('q.updated_at > ?', date($exeFromDate))
                ->where('q.updated_at < ?', date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_now - self::SESSION_TIMEOUT))
                ->where('q.items_count>0')
                ->where('q.customer_email IS NOT NULL OR a.email IS NOT NULL')
                ->where('i.parent_item_id IS NULL')
                //->where("q.entity_id NOT IN (SELECT object_id FROM aw_followup_queue awq WHERE awq.object_id = q.entity_id AND status='R')")
                ->group('q.entity_id')
                ->order('updated_at');
        $carts = $read->fetchAll($select);		
        if (!count($carts))
            return;

        $queue = Mage::getResourceModel('followupemail/queue');
        // foreach ($carts as $cart)
            // $queue->cancelByEvent($cart['customer_email'], AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW, $cart['quote_id']);

        $ruleIds = Mage::getModel('followupemail/mysql4_rule')->getRuleIdsByEventType(AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW);
        if (!count($ruleIds))
            return;

        $product = Mage::getModel('catalog/product');

        $select = $read->select()
                ->distinct()
                ->from($resource->getTableName('sales/quote_item_option'), 'value')
                ->where('code="product_type"');
				
        foreach ($carts as $cart) {
			 if (isset($cart["status"]) && $cart["status"] == "R"){
				// $queue->cancelByEvent($order->getCustomerEmail(), AW_Followupemail_Model_Source_Rule_Types::CANCEL_TYPE_CUSTOMER_PLACED_NEW_ORDER, $order->getId());
				
				 // $db = $this->_getReadAdapter();
				 // $query = "delete FROM aw_followup_queue awq WHERE id = awq.id AND status='R'";
				 // $db->query($query);
			
			}else{							
				$categoryIds = '';
				$productTypeIds = array();
				$sku = array();
				$productIds = explode(',', $cart['product_ids']);
				$extraInfo = $read->fetchCol($select->where('item_id IN (' . $cart['item_ids'] . ')'));

				foreach ($extraInfo as $productTypeId)
					$productTypeIds[$productTypeId] = true;

				foreach ($productIds as $productId) {
					$product->unsetData()->load($productId);
					if (is_array($product->getCategoryIds()))
						$categoryIds .= ',' . implode(',', $product->getCategoryIds());
					else
						$categoryIds .= ',' . $product->getCategoryIds();
					$productTypeIds[$product->getTypeId()] = true;
					$sku[] = $product->getSku();
				}

				$params = array();
				$params['store_id'] = $cart['store_id'];
				$params['customer_id'] = $cart['customer_id'];
				$params['customer_email'] = $cart['customer_email'];
				$params['category_ids'] = Mage::helper('followupemail')->noEmptyValues(array_unique(explode(',', $categoryIds)));
				$params['product_type_ids'] = array_keys($productTypeIds);
				$params['sku'] = $sku;
				$params['product_ids'] = $productIds;
				$params['object_id'] = $cart['quote_id'];
				$params['quote_id'] = $cart['quote_id'];

				foreach ($ruleIds as $ruleId) {
					AW_Followupemail_Model_Log::log('carts abandoned quoteId=' . $cart['quote_id'] . ' validating, ruleId=' . $ruleId);

					$objects = array();
					$objects['customer_firstname'] = $cart['customer_firstname'];
					$objects['customer_middlename'] = $cart['customer_middlename'];
					$objects['customer_lastname'] = $cart['customer_lastname'];
					$objects['updated_at'] = $cart['updated_at'];
					$objects['customer_is_guest'] = (int) !$cart['customer_id'];

					Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects); //add
				}
			}
        }
    }

    protected function _checkCustomerLastActivity() {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('log/visitor')->getCollection();

        if (Mage::helper('followupemail')->checkExtensionVersion('Mage_Log', '0.7.7', '<=')) {
            $collection->getSelect()->from(array('main_table' => $resource->getTableName('log/visitor')), array('first_visit_at', 'last_visit_at', 'last_url_id', 'store_id'));
        }

        $collection->getSelect()->join(array('c' => $resource->getTableName('log/customer')), 'main_table.visitor_id=c.visitor_id', array('customer_id', 'login_at', 'logout_at', 'store_id'))
                ->where('last_visit_at BETWEEN "' . $this->_lastExecTimeMySQL . '" AND "' . $this->_nowMySQL . '"')
				->where("c.customer_id NOT IN (SELECT object_id FROM aw_followup_queue awq WHERE awq.object_id = c.customer_id AND status='R')")
                ->joinLeft(array('u' => $resource->getTableName('log/url_info_table')), 'main_table.last_url_id=u.url_id', 'url')
                ->group('c.customer_id');
        $this->_processCustomerActivity($collection, AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LAST_ACTIVITY);
    }

    protected function _checkCustomerLogin() {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('log/visitor')->getCollection();

        if (Mage::helper('followupemail')->checkExtensionVersion('Mage_Log', '0.7.7', '<=')) {
            $collection->getSelect()->from(array('main_table' => $resource->getTableName('log/visitor')), array('first_visit_at', 'last_visit_at', 'last_url_id', 'store_id'));
        }

        $collection->getSelect()->join(array('c' => $resource->getTableName('log/customer')), 'main_table.visitor_id=c.visitor_id', array('customer_id', 'login_at', 'logout_at', 'store_id'))
                ->where('`login_at` BETWEEN "' . $this->_lastExecTimeMySQL . '" AND "' . $this->_nowMySQL . '" ')
				//->where("`login_at` BETWEEN '2013-12-13 00:11:54' and '2013-12-13 23:53:10' ")				
                ->group('c.customer_id');

        $this->_processCustomerActivity($collection, AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LOGGED_IN);
    }

    protected function _processCustomerActivity($collection, $ruleType) {        
		$queue = Mage::getResourceModel('followupemail/queue');
        foreach ($collection as $visit) {			
			//Zend_Debug::Dump($visit->getVisitorId()); die;            
			$queue->cancelByEvent(
                    $queue->getEmailByCustomerId($visit['customer_id']), $ruleType, $visit['customer_id']);
			
        }
		//Zend_Debug::Dump($ruleType);
		
        $ruleIds = Mage::getModel('followupemail/mysql4_rule')
                ->getRuleIdsByEventType($ruleType);

        if (!count($ruleIds))
            return;

        $params = array();
        $objects = array();
		
		//Zend_Debug::Dump($collection);
        foreach ($collection as $visit) {
			$params['customer_id'] = $visit['customer_id'];
            $params['store_id'] = $visit['store_id'];
            $objects['object_id'] = $visit['customer_id'];
            
			// for login
            if (isset($visit['login_at']))
                $objects['last_login_at'] = $visit['login_at'];
				
            // next two for logout
            if (isset($visit['last_visit_at']))
                $objects['last_visit_time'] = $visit['last_visit_at'];
            if (isset($visit['url']))
                $objects['url_last_visited'] = $visit['url'];
			
			//================================================================================	
			
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('core_read');
			$select = $read->select()
					->from(array('fq' => $resource->getTableName('followupemail/queue')))
					->joinInner(array('fr' => $resource->getTableName('followupemail/rule')), 'fq.rule_id=fr.id')
					->where('fq.`status` = "R" ')
					->where('fq.object_id = ?', $objects['object_id'])		
					->where('fr.event_type = ?', $ruleType);
			$queuelist = $read->fetchRow($select);			
			
			if (!isset ($queuelist["status"])){
				foreach ($ruleIds as $ruleId) {
					AW_Followupemail_Model_Log::log('customer last activity' . " customerId={$params['customer_id']} validating, ruleId=$ruleId");
					Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
				}		
			}
			else {
			}
        }
    }

    /*
     * Checks for new customers registered
     * deprecated with new method AW_Followupemail_Model_Events::checkCustomer($event)
     */
    /* protected function _checkNewCustomer() {
      if(self::DEBUG_MODE) AW_Followupemail_Model_Log::log('Checking new customers');

      $_count = Mage::getModel('customer/customer')
      ->getCollection()
      ->addAttributeToSelect('*')
      ->addAttributeToFilter('created_at',array('from'=>$this->_lastExecTimeMySQL,'to'=>$this->_nowMySQL))
      ->count();
      $notconfirmed_count = 0;
      $notconfirmed_count = Mage::getModel('customer/customer')
      ->getCollection()
      ->addAttributeToSelect('*')
      ->addAttributeToFilter('confirmation',array('notnull'=>false))
      ->addAttributeToFilter('created_at',array('from'=>$this->_lastExecTimeMySQL,'to'=>$this->_nowMySQL))
      ->count();
      $_count=$_count-$notconfirmed_count;
      if(!$_count) return;

      $_pages = ceil($_count / self::ITEMS_PER_ONCE);
      $currentPage = 1;
      if(self::DEBUG_MODE) AW_Followupemail_Model_Log::log("Processing New Customers. Total {$_count} customers found");

      while($currentPage <= $_pages) {
      if(self::DEBUG_MODE) AW_Followupemail_Model_Log::log("Processing New Customers. Page {$currentPage} of {$_pages} total");
      $customers_collection = Mage::getModel('customer/customer')
      ->getCollection()
      ->addAttributeToSelect('*')
      ->addAttributeToFilter('created_at',array('from'=>$this->_lastExecTimeMySQL,'to'=>$this->_nowMySQL))
      ->setPageSize(self::ITEMS_PER_ONCE)->setCurPage($currentPage);

      if(!count($customers_collection)) return;

      $queue = Mage::getResourceModel('followupemail/queue');
      foreach($customers_collection as $customer)
      $queue->cancelByEvent(
      $queue->getEmailByCustomerId($customer['entity_id']),
      AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW,
      $customer['entity_id']
      );

      $ruleIds = Mage::getModel('followupemail/mysql4_rule')
      ->getRuleIdsByEventType(AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW);

      if(!count($ruleIds)) return;

      $params = array();
      $objects = array();

      foreach($customers_collection as $customer) {
      if(!$customer->getConfirmation())
      {
      $params['customer_id'] = $customer['entity_id'];
      $params['store_id'] = $customer['store_id'];

      $objects['object_id'] = $customer['entity_id'];

      foreach($ruleIds as $ruleId) {
      AW_Followupemail_Model_Log::log('customer registered, customerId='.$params['customer_id']." validating, ruleId=$ruleId");

      Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
      }
      }
      }

      $currentPage++;
      }
      } */
}
