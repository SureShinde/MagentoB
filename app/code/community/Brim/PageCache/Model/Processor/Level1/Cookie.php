<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file Brim-LLC-Magento-License.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_PageCache
 * @copyright  Copyright (c) 2011-2014 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */


class Brim_PageCache_Model_Processor_Level1_Cookie extends Mage_Core_Model_Cookie {

    protected $_params = null;

    public function __construct($params=array()) {
        $this->_params = $params;
    }

    public function getStore() {
        return 'default';
    }

    public function getDomain() {
        return array_key_exists('domain', $this->_params) ? $this->_params['domain'] : parent::getDomain();
    }

    public function getPath() {
        return array_key_exists('path', $this->_params) ? $this->_params['path'] : parent::getPath();
    }

    public function getLifetime() {
        return array_key_exists('lifetime', $this->_params) ? $this->_params['lifetime'] : parent::getLifetime();
    }

    public function getHttponly() {
        return array_key_exists('httponly', $this->_params) ? $this->_params['httponly'] : parent::getHttponly();
    }

    public function isSecure() {
        return false;
    }

    public function setParams($params=array()) {
        $this->_params = $params;
        return $this;
    }
}