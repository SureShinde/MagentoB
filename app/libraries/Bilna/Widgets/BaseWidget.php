<?php
namespace Frontend\Widgets;
/**
 * Description of BaseWidget
 *
 * @author mariovalentino
 */

use Bilna\Libraries\BilnaRedis;
//use Bilna\Libraries\BilnaUrl;

abstract class BaseWidget extends \Phalcon\DI\Injectable {
    protected $di;
    protected $config;
    protected $config_apps;
    protected $params;
    protected $redis;
    protected $type = 'widget';
    protected $url;

    /**
     * constructor method.
     * @param array $params.
     */
    public function __construct($params = array ()) {
        $this->params       = $params;
        $this->di           = \Phalcon\DI::getDefault();
        $this->config       = $this->di->getConfig();
        $this->config_apps  = $this->di->getConfig_apps();
        $this->redis        = new BilnaRedis($this->type);
        //$this->url          = new BilnaUrl();
    }
    
    /**
     * get content widget.
     */
    public abstract function getContent($return = FALSE);
    
    /**
     * getting data from redis.
     */
    protected function getRedisData($hashName, $hashKey) {
        return $this->redis->getRedisData($hashName, $hashKey);
    }
    
    /**
     * getting data from redis.
     */
    protected function setRedisData($hashName, $hashKey, $hashValue) {
        return $this->redis->setRedisData($hashName, $hashKey, $hashValue);
    }
    
    /**
     * getting data from redis.
     */
    protected function deleteRedisData($hashName, $hashKey) {
        return $this->redis->deleteRedisData($hashName, $hashKey);
    }
    
    /**
     * setting view for widget.
     */
    public function setView($file, $data = array()){
        $view = $this->di->getView();
        $view->start();
        foreach($data as $key => $value){
            $view->setVar($key, $value);
        }
        $view->render('widgets', $file);
        $view->finish();
        return $view->getContent();
    }
}
