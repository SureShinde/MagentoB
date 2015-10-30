<?php
/**
 * Description of Bilna_Rest_Helper_Redis
 *
 * @project LOGAN
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Helper_Redis extends Mage_Core_Helper_Abstract {
    protected $redis;
    protected $host;
    protected $port;
    protected $auth;
    protected $db;
    
    public function __construct() {
        $this->init();
    }
    
    protected function init() {
        $host = '192.168.2.7';
        $port = 6379;
        $auth = '';
        $db = 13;
        
        $redis = new Redis();
        $redis->pconnect($host, $port);
        
        if ($auth) {
            $redis->auth($auth);
        }
        
        if ($db) {
            $redis->select($db);
        }
    
        $this->redis = $redis;
    }
    
    public function saveCache($key, $hashKey, $hashValue) {
        if (is_array($hashValue) || is_object($hashValue)) {
            $hashValue = json_encode($hashValue);
        }

        return $this->redis->hSet($key, $hashKey, $hashValue);
    }
    
    public function removeCache($key, $hashKey) {
        return $this->redis->hDel($key, $hashKey);
    }

    public function getCacheAll($key) {
        return $this->redis->hGetAll($key);
    }
}
