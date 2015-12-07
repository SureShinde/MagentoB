<?php
/**
 * Description of Bilna_Worker_Abstract
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Worker_Abstract {
    const DEFAULT_STORE_ID = 1;
    
    protected $_includeMage = true;
    protected $_rootPath;
    protected $_appCode = 'admin';
    protected $_appType = 'store';
    protected $_args = array ();
    protected $_factory;
    protected $_logPath = '';
    
    //- MySQL Connection
    protected $_dbResource;
    protected $_dbRead;
    protected $_dbWrite;
    
    //- Queue Service
    protected $_queueSvc;
    protected $_queueTask;
    
    //- Timer
    private $_formatDate = 'd-M-Y H:i:s';
    private $_start;
    private $_stop;

    public function __construct() {
        if ($this->_includeMage) {
            require_once $this->_getRootPath() . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
            Mage::app($this->_appCode, $this->_appType);
        }
        
        $this->_factory = new Mage_Core_Model_Factory();
        $this->_applyPhpVariables();
        $this->_parseArgs();
        $this->_construct();
        $this->_validate();
        $this->_showHelp();
    }
    
    protected function _getRootPath() {
        if (is_null($this->_rootPath)) {
            $this->_rootPath = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
        }
        
        return $this->_rootPath;
    }
    
    protected function _applyPhpVariables() {
        $htaccess = $this->_getRootPath() . '.htaccess';
        
        if (file_exists($htaccess)) {
            $data = file_get_contents($htaccess);
            $matches = array ();
            preg_match_all('#^\s+?php_value\s+([a-z_]+)\s+(.+)$#siUm', $data, $matches, PREG_SET_ORDER);
            
            if ($matches) {
                foreach ($matches as $match) {
                    @ini_set($match[1], str_replace("\r", '', $match[2]));
                }
            }
            
            preg_match_all('#^\s+?php_flag\s+([a-z_]+)\s+(.+)$#siUm', $data, $matches, PREG_SET_ORDER);
            
            if ($matches) {
                foreach ($matches as $match) {
                    @ini_set($match[1], str_replace("\r", '', $match[2]));
                }
            }
        }
    }

    protected function _parseArgs() {
        $current = null;
        
        foreach ($_SERVER['argv'] as $arg) {
            $match = array ();
            
            if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
                $current = $match[1];
                $this->_args[$current] = true;
            }
            else {
                if ($current) {
                    $this->_args[$current] = $arg;
                }
                elseif (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                    $this->_args[$match[1]] = true;
                }
            }
        }
        
        return $this;
    }

    protected function _construct() {
        return $this;
    }
    
    protected function _validate() {
//        if (isset ($_SERVER['REQUEST_METHOD'])) {
//            die ('This script cannot be run from Browser. This is the shell script.');
//        }
    }

    abstract public function run();

    protected function _showHelp() {
        if (isset ($this->_args['h']) || isset ($this->_args['help'])) {
            die ($this->usageHelp());
        }
    }

    public function usageHelp() {
        return <<<USAGE
Usage:  php -f script.php -- [options]

  -h            Short alias for help
  help          This help
USAGE;
    }
    
    public function getArg($name) {
        if (isset ($this->_args[$name])) {
            return $this->_args[$name];
        }
        
        return false;
    }
    
    protected function _start() {
        $this->_logProgress('<<-START->>');
        $this->_start = date($this->_formatDate);
        $this->_setStore();
        $this->_dbConnect();
        $this->_queueConnect();
    }
    
    protected function _stop() {
        $this->_queueDisconnect();
        $this->_stop = date($this->_formatDate);
        $this->_logProgress(sprintf("Start at %s and stop at %s", $this->_start, $this->_stop));
        $this->_logProgress($this->_getInterval($this->_start, $this->_stop));
        $this->_logProgress('<<-STOP->>');
    }

    protected function _setStore() {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }

    protected function _dbConnect() {
        $this->_dbResource = Mage::getSingleton('core/resource');
        $this->_dbRead = $this->_dbResource->getConnection('core_read');
        $this->_dbWrite = $this->_dbResource->getConnection('core_write');
    }

    protected function _queueConnect() {
        $this->_queueSvc = Mage::getModel('bilna_queue/rabbitmq');
        
        if (!$this->_queueSvc->isEnabled()) {
            $this->_critical('Queue service disabled.');
        }
    }
    
    protected function _queueDisconnect() {
        $this->_queueSvc->disconnect();
    }
    
    protected function _getInterval($start, $stop) {
        $diff = abs(strtotime($stop) - strtotime($start));
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
        $hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
        $minutes = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
        $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minutes*60));
        
        return sprintf("%d years, %d months, %d days, %d hours, %d minutes, %d seconds", $years, $months, $days, $hours, $minutes, $seconds);
    }

    protected function _critical($message) {
        $this->_logProgress($message);
        $this->_stop();
        exit(1);
    }
    
    protected function _logProgress($message) {
        //Mage::log($message, null, $this->_logPath . '.log');
        
        if ($this->getArg('verbose')) {
            $now = date($this->_formatDate);
            
            echo "[{$now}] {$message}\n";
        }
    }
}