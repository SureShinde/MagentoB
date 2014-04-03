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


class Brim_PageCache_Helper_Mobile {
    /**
     * Performs a mobile check by user agent.  Used when generating cache key ids.  Added to support design exception
     * implementations of mobile stores.
     *
     * @return string
     */
    static public function isMobile($userAgentPattern=null) {

        if ($userAgentPattern === null) {
            if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_MOBILE_ENABLE)) {
                $userAgentPattern = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_MOBILE_USER_AGENT);
            } else {
                return null;
            }
        }

        if (!empty($_SERVER['HTTP_USER_AGENT']) && !empty($userAgentPattern)) {
            if (@preg_match("/$userAgentPattern/", $_SERVER['HTTP_USER_AGENT'])) {
                return 'mobile';
            }
        }

        return 'desktop';
    }
}