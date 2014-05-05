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
 *
 * Adapted from https://code.google.com/p/mutex-for-php/
 */

class RocketWeb_Netsuite_Model_Mutex {
    var $writeablePath = '';
    var $lockName = '';
    var $fileHandle = null;

    public function __construct($lockName, $writeablePath = null){
        $this->lockName = preg_replace('/[^a-z0-9]/', '', $lockName);
        if($writeablePath == null){
            $this->writeablePath = $this->findWriteablePath();
        }
    }

    public function getLock(){
        return flock($this->getFileHandle(), LOCK_EX | LOCK_NB);
    }

    public function getFileHandle(){
        if($this->fileHandle == null){
            $this->fileHandle = fopen($this->getLockFilePath(), 'c');
        }
        return $this->fileHandle;
    }

    public function releaseLock(){
        $success = flock($this->getFileHandle(), LOCK_UN);
        fclose($this->getFileHandle());
        return $success;
    }

    public function getLockFilePath(){
        return $this->writeablePath . DIRECTORY_SEPARATOR . $this->lockName;
    }

    public function isLocked(){
        $fileHandle = fopen($this->getLockFilePath(), 'c');
        $canLock = flock($fileHandle, LOCK_EX | LOCK_NB);
        if($canLock){
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
            return false;
        } else {
            fclose($fileHandle);
            return true;
        }
    }

    public function findWriteablePath(){
        $tmpDirPath = '';
        if(function_exists('sys_get_temp_dir')) $tmpDirPath = sys_get_temp_dir();
        if(empty($tmpDirPath)) $tmpDirPath = '/tmp';
        $this->writeablePath = $tmpDirPath;
        return $this->writeablePath;
    }
}