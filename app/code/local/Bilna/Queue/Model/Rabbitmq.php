<?php
/**
 * Description of Bilna_Queue_Model_Rabbitmq
 *
 * @author Bilna Development Team <development@bilna.com>
 * @date 03-Nov-2015
 */

require_once Mage::getBaseDir() . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Bilna_Queue_Model_Rabbitmq extends Mage_Core_Model_Abstract {
    public $connection;
    public $channel;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function isEnabled() {
        if (Mage::getStoreConfig('bilna_queue/rabbitmq_settings/enabled')) {
            return true;
        }
        
        return false;
    }

    public function connect($task) {
        $hostname = Mage::getStoreConfig('bilna_queue/rabbitmq_settings/hostname');
        $port = Mage::getStoreConfig('bilna_queue/rabbitmq_settings/port');
        $username = Mage::getStoreConfig('bilna_queue/rabbitmq_settings/username');
        $password = Mage::getStoreConfig('bilna_queue/rabbitmq_settings/password');
        
        $this->connection = new AMQPStreamConnection($hostname, $port, $username, $password);
        $this->channel = $this->connection->channel();
        $this->channel->confirm_select();
        $this->channel->queue_declare($task, false, true, false, false);
    }
    
    public function publish($key, $data) {
        try {
            $msg = new AMQPMessage($data, array ('delivery_mode' => 2));
            $this->channel->basic_publish($msg, '', $key);
            
            return true;
        }
        catch (Exception $ex) {
            Mage::logException($ex->getMessage());
            
            return false;
        }
    }
    
    public function disconnect() {
        $this->channel->close();
        $this->connection->close();
    }
}
