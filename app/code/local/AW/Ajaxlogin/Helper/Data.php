<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxlogin
 * @version    1.0.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * 
 */
class AW_Ajaxlogin_Helper_Data extends Mage_Core_Helper_Abstract {
    
    /**
     * 
     */
    const VARIABLE_CODE_BASEURL                    = '{base_url}';
    const VARIABLE_CODE_BASEURL_SECURE             = '{base_url_secure}';
    const VARIABLE_CODE_HINT                       = '{hint_link}';
    
    const HANDLER_CUSTOMERACCOUNTLOGIN             = 'customer_account_login';
    const HANDLER_CHECKOUTONEPAGEINDEX             = 'checkout_onepage_index';
    const HANDLER_CHECKOUTMULTISHIPPINGLOGIN       = 'checkout_multishipping_login';
    
    const LAYOUT_HANDLER_DEFAULT                   = 'ajaxlogin_default';
    const LAYOUT_HANDLER_CUSTOMERACCOUNTLOGIN      = 'ajaxlogin_customer_account_login';
    const LAYOUT_HANDLER_CHECKOUTONEPAGEINDEX      = 'ajaxlogin_checkout_onepage_index';
    
    const XML_CONFIG_PATH_GENERAL_MODULE_ENABLED   = 'ajaxlogin/general/module_enabled';
    const XML_CONFIG_PATH_LOGINFORM_LOGIN_LANDING  = 'ajaxlogin/login_form/login_success_landing_page';
    const XML_CONFIG_PATH_LOGINFORM_LOGOUT_LANDING = 'ajaxlogin/login_form/logout_success_landing_page';
    const XML_CONFIG_PATH_REGISTERFORM_LANDING     = 'ajaxlogin/registration_form/success_landing_page';
    const XML_CONFIG_PATH_REGISTERFORM_NEWSLETTER  = 'ajaxlogin/registration_form/display_newsletter_subscription_section';
    const XML_CONFIG_PATH_REGISTERFORM_TERMS       = 'ajaxlogin/registration_form/display_terms_and_conditions';
    const XML_CONFIG_PATH_RECOVERYFORM_LANDING     = 'ajaxlogin/password_recovery_form/success_landing_page';
    
    
    /**
     * 
     */
    private $__uid                               = 1;
    
    
    /**
     * 
     */
    public function getUniqueID() {
        $this->__uid++;
        
        return 'ajaxlogin-' . $this->__uid;
    }
    
    
    /**
     * 
     */
    public function getNetworks() {
        $__configData = Mage::getConfig()->getNode('global/ajaxlogin/authorization');
        if ( is_object($__configData) ) $__configData = (array)$__configData;
        
        $__networks = array();
        if ( is_array($__configData) ) {
            foreach ( $__configData as $__key => $__info ) {
                if ( (!$__info->config_path_enabled) or ( Mage::getStoreConfig((string)$__info->config_path_enabled) ) ) {
                    $__network = new Varien_Object();
                    
                    $__network->setName((string)$__key);
                    $__network->setTitle((string)$__info->title);
                    $__network->setModel((string)$__info->model);
                    $__network->setBlock((string)$__info->block);
                    $__network->setTemplate((string)$__info->template);
                    $__network->setThumbnailImagePath((string)$__info->thumbnail_image_path);
                    $__network->setButtonHtmlId($this->getUniqueId());
                    
                    array_push($__networks, $__network);
                }
            }
        }
        
        return $__networks;
    }
    
    
    /**
     * 
     */
    public function getConfigLanding($path) {
        $__configValue = Mage::getStoreConfig($path);
        $__configValue = str_replace(AW_Ajaxlogin_Helper_Data::VARIABLE_CODE_BASEURL, Mage::getBaseUrl(), $__configValue);
        $__configValue = str_replace(AW_Ajaxlogin_Helper_Data::VARIABLE_CODE_BASEURL_SECURE, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true), $__configValue);
        
        return $__configValue;
    }
    
    
    /**
     * 
     */
    public function getUrlSafeForAjax($action) {
        $__url = Mage::getUrl($action);
        
        $__urlScheme = parse_url($__url, PHP_URL_SCHEME);
        if ( Mage::app()->getRequest()->isSecure() and ($__urlScheme == 'http') ) {
            $__url = str_replace('http://', 'https://', $__url);
        }
        if ( !Mage::app()->getRequest()->isSecure() and ($__urlScheme == 'https') ) {
            $__url = str_replace('https://', 'http://', $__url);
        }
        
        return $__url;
    }
    
    
    /**
     * 
     */
    public function addDataToCookie($key, $data) {
        $__serializedValue = Mage::getSingleton('core/cookie')->get('ajaxlogin-oauth');
        $__value = @unserialize($__serializedValue);
        if ( !is_array($__value) ) $__value = array();
        $__value[$key] = $data;
        $__value = serialize($__value);
        Mage::getSingleton('core/cookie')->set('ajaxlogin-oauth', $__value);
        
        return $this;
    }
    
    
    /**
     * 
     */
    public function fetchDataFromCookie($key) {
        $__serializedValue = Mage::getSingleton('core/cookie')->get('ajaxlogin-oauth');
        $__value = @unserialize($__serializedValue);
        if ( !is_array($__value) ) $__value = array();
        return isset($__value[$key]) ? $__value[$key] : null;
    }
}