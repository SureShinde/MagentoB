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
class RocketWeb_Netsuite_Helper_Viewinnetsuite extends Mage_Core_Helper_Abstract {

    public function getNetsuiteFrontendBaseUrl() {
        return Mage::getStoreConfig('rocketweb_netsuite/general/netsuite_base_url');
    }

    public function getViewInNetsuiteUrl($urlPattern,$internalId,$type = '') {
        $urlPattern = str_replace('{{ID}}',$internalId,$urlPattern);
        if($type == 'current_invoice') {
            $path = '';
            if(Mage::helper('rocketweb_netsuite')->getInvoiceTypeInNetsuite() == RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Invoicenetsuitetype::TYPE_INVOICE) {
                $path = 'custinvc';
            }
            else {
                $path = 'cashsale';
            }
            $urlPattern = str_replace('{{object}}',$path,$urlPattern);
        }
        return $this->getNetsuiteFrontendBaseUrl().$urlPattern;
    }
}