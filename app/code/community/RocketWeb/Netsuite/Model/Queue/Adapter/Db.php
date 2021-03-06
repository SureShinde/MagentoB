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
class RocketWeb_Netsuite_Model_Queue_Adapter_Db extends Zend_Queue_Adapter_Db {

    /**
     * Get messages in the queue
     *
     * @param  integer    $maxMessages  Maximum number of messages to return
     * @param  integer    $timeout      Visibility timeout for these messages
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message_Iterator
     * @throws Zend_Queue_Exception - database error
     */
    public function receive($maxMessages = null, $timeout = null, Zend_Queue $queue = null)
    {
        if ($maxMessages === null) {
            $maxMessages = 1;
        }
        if ($timeout === null) {
            $timeout = self::RECEIVE_TIMEOUT_DEFAULT;
        }
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $msgs      = array();
        $info      = $this->_messageTable->info();
        $microtime = microtime(true); // cache microtime
        $db        = $this->_messageTable->getAdapter();

        // start transaction handling
        try {
            if ( $maxMessages > 0 ) { // ZF-7666 LIMIT 0 clause not included.
                /* addition by Willy */
                $run_mode = Mage::registry('current_run_mode');
                $run_recordtype = Mage::registry('current_run_recordtype');
                $importWhereCondition = '1 = 1';

                // if mode is import
                if ($run_mode == 'import')
                {
                    // call the function whose job is to get the where condition for the import
                    $importWhereCondition = $this->get_import_where_condition(Mage::registry('current_importable_entities'));
                }
                /* end of addition by Willy */

                $db->beginTransaction();

                $query = $db->select();
                if ($this->_options['options'][Zend_Db_Select::FOR_UPDATE]) {
                    // turn on forUpdate
                    $query->forUpdate();
                }
                $query->from($info['name'], array('*'))
                    ->where('queue_id=?', $this->getQueueId($queue->getName()))
                    ->where('handle IS NULL OR timeout+' . (int)$timeout . ' < ' . (int)$microtime)
                    ->where($importWhereCondition) // addition by Willy
                    ->order('priority ASC', 'created ASC')
                    ->limit($maxMessages);

                foreach ($db->fetchAll($query) as $data) {
                    // setup our changes to the message
                    $data['handle'] = md5(uniqid(rand(), true));

                    $update = array(
                        'handle'  => $data['handle'],
                        'timeout' => $microtime,
                    );

                    // update the database
                    $where   = array();
                    $where[] = $db->quoteInto('message_id=?', $data['message_id']);
                    $where[] = 'handle IS NULL OR timeout+' . (int)$timeout . ' < ' . (int)$microtime;

                    $count = $db->update($info['name'], $update, $where);

                    // we check count to make sure no other thread has gotten
                    // the rows after our select, but before our update.
                    if ($count > 0) {
                        $msgs[] = $data;
                    }
                }
                $db->commit();
            }
        } catch (Exception $e) {
            $db->rollBack();

            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
        }

        $options = array(
            'queue'        => $queue,
            'data'         => $msgs,
            'messageClass' => $queue->getMessageClass(),
        );

        $classname = $queue->getMessageSetClass();
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }



    /********************************************************************
     * Messsage management functions
     *********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param  string     $message Message to send to the active queue
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Exception - database error
     */
    public function send($message, Zend_Queue $queue = null, $priority = 0, $last_modified = null)
    {
        if ($this->_messageRow === null) {
            $this->_messageRow = $this->_messageTable->createRow();
        }

        if ($queue === null) {
            $queue = $this->_queue;
        }

        if (is_scalar($message)) {
            $message = (string) $message;
        }
        if (is_string($message)) {
            $message = trim($message);
        }

        if (!$this->isExists($queue->getName())) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue does not exist:' . $queue->getName());
        }

        $msg           = clone $this->_messageRow;
        $msg->queue_id = $this->getQueueId($queue->getName());
        $msg->created  = time();
        $msg->body     = $message;
        $msg->md5      = md5($message);
        $msg->priority = $priority;
        $msg->last_modified = $last_modified;
        // $msg->timeout = ??? @TODO

        try {
            $msg->save();
        } catch (Exception $e) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
        }

        $options = array(
            'queue' => $queue,
            'data'  => $msg->toArray(),
        );

        $classname = $queue->getMessageClass();
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    private function get_import_where_condition($importableEntities)
    {
        $importWhereCondition = '';

        // building where conditions
        if (count($importableEntities) > 0)
        {
            $importWhereCondition .= "(";
            $first_loop = true;
            foreach ($importableEntities as $path => $name) {
                if (!$first_loop)
                    $importWhereCondition .= " or ";

                switch ($path) {
                    case "inventoryitem":
                        $importWhereCondition .= "(body like 'inventoryitem%')";
                        break;
                    case "order":
                        $importWhereCondition .= "(body like 'order%' and body not like 'order_fulfillment%')";
                        break;
                    case "order_fulfillment":
                        $importWhereCondition .= "(body like 'order_fulfillment%')";
                        break;
                    case "invoice":
                        $importWhereCondition .= "(body like 'invoice%' or body like 'cashsale%')";
                        break;
                    case "proformainvoice":
                        $importWhereCondition .= "(body like 'proformainvoice%')";
                        break;
                    case "requestorder":
                        $importWhereCondition .= "(body like 'requestorder%')";
                        break;
                    case "cashsale":
                        $importWhereCondition .= "(body like 'cashsale%')";
                        break;
                }

                $first_loop = false;
            }
            $importWhereCondition .= ")";
        }

        return $importWhereCondition;
    }
}
