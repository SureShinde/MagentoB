<?php
/**
 * Description of commonShellScripts
 *
 * @path    shell/bilna/commonShellScripts.php
 * @author  Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

abstract class commonShellScripts extends Mage_Shell_Abstract {
  protected $resource;
  protected $write;
  protected $read;
  protected $logfile;
  protected $lockfile_time_limit_in_seconds;
  protected $_lockFile = null;
  protected $process_id;

  public function init() {
    $this->resource = Mage::getSingleton('core/resource');
    $this->write = $this->resource->getConnection('core_write');
    $this->read = $this->resource->getConnection('core_read');
  }

  public function set_logfile($filename) {
    $this->logfile = $filename;
  }

  public function set_lockfile_timelimit($timelimit) {
    $this->lockfile_time_limit_in_seconds = $timelimit;
  }

  public function set_process_id($process_id) {
    $this->process_id = $process_id;
  }

  public function get_process_id() {
    return $this->process_id;
  }

  public function _isLocked() {
    if ($this->_lockFile == null) {
      $this->_lockFile = $this->_getLockFile();
    }

    if (file_exists($this->_lockFile)) {
      $handle = fopen($this->_lockFile, 'r');
      $content = fread($handle, filesize($this->_lockFile));
      fclose($handle);

      $age_of_lock_file = strtotime($content);
      $now = Mage::getModel('core/date')->timestamp(time());
      if ($now < $age_of_lock_file + $this->lockfile_time_limit_in_seconds) {
        return true;
      }
    }

    //create lock file
    $this->_lock();

    return false;
  }

  protected function _getLockFile() {
    $varDir = Mage::getConfig()->getVarDir('locks');
    $this->_lockFile = $varDir . DS . $this->process_id . '.lock';

    return $this->_lockFile;
  }

  public function _lock() {
    $handle = fopen($this->_lockFile, 'w');
    $content = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

    fwrite($handle, $content);
    fclose($handle);
  }

  public function _unlock() {
    if (file_exists($this->_lockFile)) {
      unlink($this->_lockFile);

      return true;
    }

    return false;
  }

  public function logProgress($message, $veritrans_log = false) {
    if ($veritrans_log) {
        $this->writeLogVeritrans($message);
    } else {
        $this->writeLog($message);
    }

    if ($this->getArg('verbose')) {
        echo $message . "\n";
    }
  }

  public function writeLog($message) {
    Mage::log($message, null, $this->logfile);
  }

  public function writeLogVeritrans($message) {
      Mage::log($message, null, 'veritrans_status.log');
  }
}