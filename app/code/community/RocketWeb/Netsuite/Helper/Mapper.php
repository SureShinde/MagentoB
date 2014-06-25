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
class RocketWeb_Netsuite_Helper_Mapper extends Mage_Core_Helper_Data {

    /**
     * @return mixed
     */
    protected function _getNetsuiteService() {
        return Mage::helper('rocketweb_netsuite')->getNetsuiteService();
    }
    
    protected function log($message) {
        Mage::helper('rocketweb_netsuite')->log($message);
    }
}