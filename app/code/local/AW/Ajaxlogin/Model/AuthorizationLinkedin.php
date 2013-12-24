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
require_once dirname(__FILE__) . DS . 'cmplib.OAuth.php';


/**
 * 
 */
class AW_Ajaxlogin_Model_AuthorizationLinkedin extends AW_Ajaxlogin_Model_AuthorizationAbstract {
    
    /**
     * 
     */
    const CODENAME                       = 'linkedin';
    const API_MODEL                      = 'ajaxlogin/linkedin';
    const REDIRECT_ACTION                = 'ajaxlogin/oauth/acceptToken/network/linkedin';
    const PROVIDER_URI                   = 'https://api.linkedin.com/uas/oauth';
    const REQUEST_TOKEN_URI              = 'https://api.linkedin.com/uas/oauth/requestToken';
    const ACCESS_TOKEN_URI               = 'https://api.linkedin.com/uas/oauth/accessToken';
    const ACCOUNT_DATA_REQUEST_URI       = 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,email-address)';
    
    const E_MSG_NOREQUEST                = 'No request instance is assigned to authorization model';
    const E_MSG_NOTOKEN                  = 'Failed to get an access token';
    const E_MSG_NOEMAIL                  = 'Unable to fetch email address from LinkedIn account';
    const E_MSG_HTTPCLIENTERROR          = 'An error occured when making HTTP request to API';
    
    const PARAMKEY_OAUTH_EXPIRES_IN      = 'oauth_expires_in';
    const PARAMKEY_OAUTH_AUTH_EXPIRES_IN = 'oauth_authorization_expires_in';
    
    
    /**
     * 
     */
    public function isProperlyConfigured() {
        return Mage::helper('ajaxlogin/adminhtml')->isLinkedinConfigured();
    }
    
    
    /**
     * 
     */
    protected function __getConfig() {
        return
            array(
                'consumerKey'     => Mage::getStoreConfig('ajaxlogin/login_with_linkedin_account/api_key'),
                'consumerSecret'  => Mage::getStoreConfig('ajaxlogin/login_with_linkedin_account/secret_key'),
                'callbackUrl'     => Mage::getUrl(self::REDIRECT_ACTION),
                'siteUrl'         => self::PROVIDER_URI,
                'requestTokenUrl' => self::REQUEST_TOKEN_URI,
                'accessTokenUrl'  => self::ACCESS_TOKEN_URI
            )
        ;
    }
    
    
    /**
     * 
     */
    public function getAccountData() {
        return $this->__requestAccountData();
    }
    
    
    /**
     * 
     */
    public function login() {
        if ( !$this->getRequest() ) {
            throw new Exception(self::E_MSG_NOREQUEST);
        }
        
        $__accessTokenParameters = $this->__getAccessTokenFromCookies();
        if ( $__accessTokenParameters ) {
            $__accountData = $this->__requestAccountData();
            
            if ( !is_array($__accountData) ) $__accountData = array();
            $__linkedinEmail = isset($__accountData['emailAddress']) ? $__accountData['emailAddress'] : null;
            
            if ( $__linkedinEmail ) {
                return $this->__attemptToLoginEmail($__linkedinEmail);
            }
            else {
                throw new Exception(self::E_MSG_NOEMAIL);
            }
        }
        else {
            throw new Exception(self::E_MSG_NOTOKEN);
        }
    }
    
    
    /**
     * 
     */
    public function getAuthorizationStatus() {
        $__response = new Varien_Object();
        
        $__accessToken = $this->__getAccessTokenFromCookies();
        if ( $__accessToken ) {
            $__accountData = $this->__requestAccountData();
            
            $__response->setAccessTokenAccepted(1);
            $__response->setAccountData($__accountData);
        }
        else {
            try {
                $__api = Mage::getModel(self::API_MODEL);
                if ( $__api ) {
                    $__api->setConfig($this->__getConfig());
                    $__response->setAuthorizationRequestUrl( $__api->getAuthorizationUrl() );
                    $__lastRequestToken = $__api->getConsumer()->getLastRequestToken();
                    $this->__putRequestTokenIntoCookies(
                        array(
                            Zend_Oauth_Token::TOKEN_PARAM_KEY                => $__lastRequestToken->getParam(Zend_Oauth_Token::TOKEN_PARAM_KEY),
                            Zend_Oauth_Token::TOKEN_SECRET_PARAM_KEY         => $__lastRequestToken->getParam(Zend_Oauth_Token::TOKEN_SECRET_PARAM_KEY),
                            Zend_Oauth_Token::TOKEN_PARAM_CALLBACK_CONFIRMED => $__lastRequestToken->getParam(Zend_Oauth_Token::TOKEN_PARAM_CALLBACK_CONFIRMED)
                        )
                    );
                }
                else {
                    throw new Exception('Failed to load network model');
                }
            }
            catch ( Zend_Oauth_Exception $__E ) {
                Mage::logException($__E);
            }
            catch ( Exception $__E ) {
                Mage::logException($__E);
            }
        }
        
        return $__response->getData();
    }
    
    
    /**
     * 
     */
    public function acceptToken($callbackParameters) {
        $__requestTokenParameters = $this->__getRequestTokenFromCookies();
        
        if ( isset($callbackParameters['oauth_token']) and ($__requestTokenParameters) ) {
            $__api = Mage::getModel(self::API_MODEL);
            if ( $__api ) {
                $__api->setConfig($this->__getConfig());
                $__requestToken = new Zend_Oauth_Token_Request();
                $__requestToken->setParams($__requestTokenParameters);
                $__accessToken = $__api->getConsumer()->getAccessToken($callbackParameters, $__requestToken);
                if ( $__accessToken ) {
                    $this->__putAccessTokenIntoCookies(
                        array(
                            Zend_Oauth_Token::TOKEN_PARAM_KEY                => $__accessToken->getParam(Zend_Oauth_Token::TOKEN_PARAM_KEY),
                            Zend_Oauth_Token::TOKEN_SECRET_PARAM_KEY         => $__accessToken->getParam(Zend_Oauth_Token::TOKEN_SECRET_PARAM_KEY),
                            Zend_Oauth_Token::TOKEN_PARAM_CALLBACK_CONFIRMED => $__accessToken->getParam(Zend_Oauth_Token::TOKEN_PARAM_CALLBACK_CONFIRMED),
                            self::PARAMKEY_OAUTH_EXPIRES_IN                  => $__accessToken->getParam(self::PARAMKEY_OAUTH_EXPIRES_IN),
                            self::PARAMKEY_OAUTH_AUTH_EXPIRES_IN             => $__accessToken->getParam(self::PARAMKEY_OAUTH_AUTH_EXPIRES_IN)
                        )
                    );
                    
                    $__accountData = $this->__requestAccountData();
                    return '<script type="text/javascript">window.opener.__linkedinData = ' . Zend_Json_Encoder::encode($__accountData) . '; window.close();</script>';
                }
            }
            else {
                throw new Exception('Failed to load network model');
            }
        }
        else {
            /**
             * User probably cancelled or closed the dialog or disallowed the application
             * to access his account, and the script receives no token
             */
        }
        
        return null;
    }
    
    
    /**
     * 
     */
    protected function __getRequestTokenFromCookies() {
        return Mage::helper('ajaxlogin/data')->fetchDataFromCookie('LINKEDIN_REQUEST_TOKEN');
    }
    
    
    /**
     * 
     */
    protected function __putRequestTokenIntoCookies($requestTokenParameters) {
        Mage::helper('ajaxlogin/data')->addDataToCookie('LINKEDIN_REQUEST_TOKEN', $requestTokenParameters);
    }
    
    
    /**
     * 
     */
    protected function __getAccessTokenFromCookies() {
        return Mage::helper('ajaxlogin/data')->fetchDataFromCookie('LINKEDIN_ACCESS_TOKEN');
    }
    
    
    /**
     * 
     */
    protected function __putAccessTokenIntoCookies($accessTokenParameters) {
        Mage::helper('ajaxlogin/data')->addDataToCookie('LINKEDIN_ACCESS_TOKEN', $accessTokenParameters);
    }
    
    
    /**
     * 
     */
    protected function __requestAccountData() {
        $__accountData = null;
        
        $__accessTokenParameters = $this->__getAccessTokenFromCookies();
        if ( $__accessTokenParameters ) {
            try {
                $__accessToken = new Zend_Oauth_Token_Access();
                $__accessToken->setParams($__accessTokenParameters);
                
                $__httpClient = $__accessToken->getHttpClient($this->__getConfig());
                $__httpClient->setUri(self::ACCOUNT_DATA_REQUEST_URI);
                $__httpClient->setMethod(Zend_Http_Client::GET);
                $__httpClient->setParameterGet('format', 'json');
                $__httpResponse = $__httpClient->request();
                $__accountData = Zend_Json::decode($__httpResponse->getBody());
            }
            catch ( Exception $__E ) {
                Mage::logException($__E);
            }
        }
        
        return $__accountData;
    }
}