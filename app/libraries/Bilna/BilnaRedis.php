<?php
/**
 * Description of BilnaRedis
 *
 * @author Bilna Development Team <development@bilna.com>
 */
namespace Bilna\Libraries;


class BilnaRedis {
    protected $di;
    protected $config;
    protected $enabled;
    protected $redis;
    protected $hashKey;
    /**
     * @param $type api|widget|lang|session
     */
    public function __construct($type) {
        $this->di       = \Phalcon\DI::getDefault();
        $this->config   = $this->di->getConfig();
        $this->enabled  = $this->config->cache->redis->enabled->$type;
        
        if ($this->enabled) {
            $this->redis = $this->di->getRedis();
        }
    }
    
    public function getAllRedisData($hashName) {
        if ($this->enabled) {
            $result = $this->redis->hGetAll($hashName);
            
            if ($result) {
                return $result;
            }
        }
        
        return array ();
    }
    
    public function getRedisData($hashName, $hashKey) {
        if ($this->enabled) {
            $result = $this->redis->hGet($hashName, $hashKey);
            
            if ($this->isJson($result)) {
                $result = json_decode($result, true);
            }
            
            return $result;
        }
        
        return false;
    }
    
    public function setRedisData($hashName, $hashKey, $hashValue, $expired = false) {
        if ($this->enabled) {
            if (is_array($hashValue)) {
                $hashValue = json_encode($hashValue);
            }
            
            $result = $this->redis->hSet($hashName, $hashKey, $hashValue);
            
            if ($expired) {
                $this->redis->setTimeout($hashName, (int) $expired);
            }
                
            return $result;
        }
        
        return true;
    }
    
    public function deleteRedisData($hashName, $hashKey = null) {
        if ($this->enabled) {
            if ($hashKey) {
                return $this->redis->hDel($hashName, $hashKey);
            }
            else {
                return $this->redis->delete($hashName);
            }
        }
        
        return true;
    }
    
    public function setRedisString($key, $value, $expired = false) {
        if ($this->enabled) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            
            $result = $this->redis->set($key, $value);
            
            if ($expired) {
                $this->redis->setTimeout($key, (int) $expired);
            }
            
            return $result;
        }
        
        return true;
    }
    
    protected function isJson($string) {
        json_decode($string);
        
        return (json_last_error() == JSON_ERROR_NONE);
    }
}