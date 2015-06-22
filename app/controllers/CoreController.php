<?php
/*
 * @author Bilna Development <development@bilna.com> 
 */
namespace Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Flash\Direct,
    Phalcon\Flash\Session,
    Phalcon\Acl;

use WriteIniFile\WriteIniFile;

use Bilna\Libraries\BilnaTranslation;
use Bilna\Libraries\BilnaStatic;
use Bilna\Libraries\BilnaRedis;


//use Backend\Core\Libraries\BackendValidator
//    ,Backend\Core\Libraries\Authentication
//	;

/**
 * Class CoreController
 * @package Frontend\Controllers
 * @property-read \Bilna\Libraries\ParallelCurl mcurl
 */
abstract class CoreController extends Controller {
    protected $_baseUrl         = NULL;
    protected $_languageDefault = NULL;
    protected $_translation     = NULL;
    protected $_language        = NULL;
    
    protected $_isCustomerLogin  = FALSE;
    protected $_isProduction     = FALSE;
    
    protected $_css              = array ();
    protected $_js               = array ();
    
    protected $_customerId                  = null;
    protected $_customerKey                 = null;
    protected $_customerData                = array ();
    protected $_customerStatus              = 0;
    protected $_customerWebsiteGroupId      = 0;
    protected $_customerPriceGroupId        = 0;
    protected $_customerBasketType          = null;
    protected $_customerBasketData          = array ();
    protected $_customerBasketVoucherCode   = '';
    
    protected $_cssJsPath;
    
    public function initialize() {
        $this->_setBaseUrl();
        $this->_setAssets();
        $this->_getTranslation();
        $this->_setCustomerSession();

        $this->__checkAPI([$this,'initialize']/*function()use($container){

            echo get_class($container);
            echo get_class($this);
        }*/);
        $this->view->customerData        = $this->_customerData;
        
        $this->view->facebookAppId       = $this->config->social->facebook->app_id;
        $this->view->googleAppId         = $this->config->social->google->app_id;
        $this->view->recaptchaAppId      = $this->config->recaptcha->app_id;
        $this->view->analyticsGtmEnabled = $this->config->analytics->gtm->enabled;
        if ($this->config->analytics->gtm->enabled) {
            $this->view->analyticsGtmContent = $this->config->analytics->gtm->content;
        }
        $this->view->isCustomerLogin = $this->_isCustomerLogin;
        $this->view->t               = $this->_translation;
        $this->view->language        = $this->_language;
    }

    protected $__checkAPI = true; // just a flag for controller may not need to request API

    /**
     * check token API
     */
    protected function __checkAPI(callable $function){
        //echo gettype($function);
        if($this->__checkAPI){

            $_isSuccess = false;
            if($this->config->api->request_token && $this->config->api->request_secret){
                $oauthClient = new \OAuth($this->config->api->customer_key, $this->config->api->customer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);
                if($this->config->environment=='development')
                $oauthClient->enableDebug();

                try{
                    $oauthClient->setToken($this->config->api->request_token, $this->config->api->request_secret);
                    $resourceUrl = $this->config->api->base_url.$this->config->api->url_api."/customers?limit=1";
//                    die(var_dump([$resourceUrl,$this->config->api->request_token, $this->config->api->request_secret,$this->config->api->customer_key, $this->config->api->customer_secret]));
                    $header = array ('Accept' => 'application/json');
                    $oauthClient->fetch($resourceUrl, array (), OAUTH_HTTP_METHOD_GET, $header);
                    if(json_decode($oauthClient->getLastResponse()))$_isSuccess = true;
                    //die(var_dump([$this->config->api->request_token, $this->config->api->request_secret,$oauthClient->debugInfo]));
                }catch(\OAuthException $e){
                    echo $e->getMessage();
                    //die(var_dump($oauthClient->debugInfo));
                }
            }

            if(!$_isSuccess){ // if rejected
                if(!class_exists('\WriteIniFile\WriteIniFile')){
                    throw new \Exception('vendor composer Magicalex/WriteIniFile Not exist');
                }

                if($this->request->getQuery('oauth_token')!==NULL/*,
                    $_GET['oauth_verifier']*/){

                    $oauthClient = new \OAuth($this->config->api->customer_key, $this->config->api->customer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
                    $oauthClient->enableDebug();
                    $accessTokenRequestUrl = $this->config->api->base_url.$this->config->api->url_token;
                    $oauthClient->setToken($this->request->getQuery('oauth_token'), $this->config->api->request_secret);//die(var_dump([$accessTokenRequestUrl,$_GET['oauth_token'], $this->config->api->request_secret]));

                    try{
                        $accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);

                        // write config(dot)ini
                        $configWriter = new WriteIniFile(__DIR__.'/../config/config.ini');

                        $configArr = [];
                        $configArr['api']['request_token']  = $accessToken['oauth_token'];
                        $configArr['api']['request_secret'] = $accessToken['oauth_token_secret'];
                        $configWriter->update($configArr);

                        $configWriter->write();

                        $getQueries = (array)$this->request->getQuery();
                        unset($getQueries['oauth_token'],$getQueries['oauth_verifier']);
                        // redirect remove oauth
                        array_walk($getQueries, function(&$item1, $key){$item1 = $key.'='.$item1;});

                        $url = $this->router->getRewriteUri();
                        if($url[0]=='/')$url = substr($url,1);

                        $this->response->redirect($url.(count($getQueries)?'?'.implode('&',$getQueries):''));
                        $this->response->sendHeaders();
                    }catch(\Exception $e){
                        echo get_class($e)."\n";
                        die(var_dump($oauthClient->debugInfo));
                    }

                }
                // request new token by init auth digest
                elseif (!isset($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
                    $this->response->setHeader('WWW-Authenticate','Basic realm="Enter Consumer Auth to Magento"');
                    $this->response->setRawHeader('HTTP/1.0 401 Unauthorized');
                    $this->response->sendHeaders();
                    echo 'Not Auth to API Magento';
                    exit;
                } else {
                    // write config(dot)ini
                    $configWriter = new WriteIniFile(__DIR__.'/../config/config.ini');

                    $configArr['api'] = $this->config->api->toArray();
                    $configArr['api']['customer_key'] = $this->request->getServer('PHP_AUTH_USER');
                    $configArr['api']['customer_secret'] = $this->request->getServer('PHP_AUTH_PW');
                    $oauthClient = new \OAuth($configArr['api']['customer_key'], $configArr['api']['customer_secret'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
                    if($this->config->environment=='development')
                    $oauthClient->enableDebug();

                    $callbackUrl = $this->request->getScheme().'://'.$this->request->getHttpHost().'/';
                    $temporaryCredentialsRequestUrl = $this->config->api->base_url.$this->config->api->url_init.'?oauth_callback='. urlencode($callbackUrl);
                    $adminAuthorizationUrl = $this->config->api->base_url.$this->config->api->url_admin_auth;


                    $requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
                    $configArr['api']['request_secret'] = $requestToken['oauth_token_secret'];
                    $configWriter->update($configArr);

                    $configWriter->write();
//die($adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']);
                    $this->response->redirect($adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token'], true);
                    $this->response->sendHeaders();
                    exit;
                }
            }
        }
        //return false;
    }

    public function beforeRender($event, $view)
    {
        die('ok');
        if(count($this->mcurl->outstanding_requests))
            $this->mcurl->finishAllRequests();
        return true;
    }
	
	public function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
	
    public function route404Action() {
        return $this->view->pick("route404");
    }

    protected function _setBaseUrl() {
//         echo "<pre>";var_dump($this->config);die;
        $this->_baseUrl         = $this->url->getBaseUri();
        $this->view->baseUri    = $this->url->getBaseUri();
        $this->view->staticUri  = $this->url->getStaticBaseUri();
        $this->view->mediaUri   = $this->config->media->url;
    }
    
    protected function _setAssets() {
        $this->_setCss();
        $this->_setLess();
        $this->_setJs();
    }

    protected function _setCss() {
        if ($this->_isProduction) {
            $defaultCss = $this->config_apps->assets->default_css;
            //$cssMin = sprintf("css/min/bilna-%s.css", $this->_version);
            $collectCss = true;
            if ($collectCss) {
                foreach ($defaultCss as $name) {
                    $css = sprintf("css/%s-%s.css", $name, $this->config->version);
                    $this->assets->addCss($css);
                }
                foreach ($this->css as $name) {
                    $css = sprintf("css/%s-%s.css", $name, $this->config->version);
                    $this->assets->addCss($css);
                }
                //$this->assets->get('css')
                //->setTargetPath($cssMin)
                //->setTargetUri($cssMin)
                //->join(true)
                //->addFilter(new Phalcon\Assets\Filters\Cssmin());
            }
        }
    }

    protected function _setLess() {
        $defaultLess    = $this->config_apps->assets->default_css;
        $additionalLess = $this->_css;
        $lessArr        = array();

        if (!$this->_isProduction) {
            if ($defaultLess) {
                foreach ($defaultLess as $name) {
                    $lessArr[] = sprintf("less/%s.less", $name);
                }
            }

            if ($additionalLess) {
                foreach ($additionalLess as $name) {
                    $lessArr[] = sprintf("less/%s.less", $name);
                }
            }
        }

        $this->view->lessArr = $lessArr;
    }

    protected function _setJs() {
        $defaultJs = $this->config_apps->assets->default_js;

        foreach ($defaultJs as $key => $position) {
            $jsMin = sprintf("js/min/%s-%s.js", $key, $this->config->version);

            foreach ($position as $name) {
                if (!$this->_excludeJs($name)) {
                    $js = sprintf("js/%s.js", $name);
                    $this->assets->collection($key)->addJs($js);
                }
            }

            if ($key == 'footer') {
                foreach ($this->_js as $name) {
                    if (!$this->_excludeJs($name)) {
                        $js = sprintf("js/%s.js", $name);
                        $this->assets->collection($key)->addJs($js);
                    }
                }
            }
            if ($this->_isProduction) {
            // $this->assets->collection($key)
            // ->setTargetPath($jsMin)
            // ->setTargetUri($jsMin)
            // ->join(true)
            // ->addFilter(new Phalcon\Assets\Filters\Jsmin());
            }
        }
    }

    protected function _excludeJs($js) {
        if ($this->_isProduction) {
            $excludeFile = $this->config_apps->assets->exclude_js;

            if ($excludeFile) {
                foreach ($excludeFile as $name) {
                    if ($name == $js) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
    
    protected function _getTranslation() {
        $this->_setCurrentLanguage();
        $redisLang = new BilnaRedis('lang');
        $message = $redisLang->getAllRedisData('lang/' . $this->_language);
        $this->_translation = new BilnaTranslation(array (
            'language' => $this->_language,
            'content' => $message,
        ));
        $this->view->translationArr = $this->config->translation->data;
    }

    protected function _setCurrentLanguage() {
        $this->_languageDefault = $this->config->translation->default;
        
        if ($this->session->has(BilnaStatic::$sessionLanguage)) {
            $this->_language = $this->session->get(BilnaStatic::$sessionLanguage);
        }
        else {
            $this->_language = $this->_languageDefault;
        }
    }
    
    protected function __($key) {
        return $this->_translation->_($key);
    }
    
    /*
     * set customer session data for user login/guest
     */
    protected function _setCustomerSession() {
        //-set customerId
        if ($this->session->has(BilnaStatic::$sessionLoginUser)) {
            $this->_isCustomerLogin     = TRUE;
            
            $this->_customerId              = $this->session->get(BilnaStatic::$sessionLoginUser);
            $this->_customerKey             = BilnaStatic::$sessionLoginUserRedis;
            $this->_customerBasketType      = BilnaStatic::$sessionBasketUserRedis;
            $this->_customerData            = $this->_getCustomerData();
            
            $this->view->firstname          = $this->_getFirstname($this->_customerData['customerName']);
            $this->view->customerDataLayer  = array (
                                                 'id'    => $this->_customerData['customerId'],
                                                 'email' => $this->_customerData['customerEmail'],
                                                 'name'  => $this->_customerData['customerName'],
                                              );
            
            if( $this->_customerData['customerForce'] && 
               ( $this->dispatcher->getActionName() != 'reset') ){
                   return $this->response->redirect('reset-password');
            }
            
        } else {
            if ($this->session->has(BilnaStatic::$sessionLoginGuest)) {
                $this->_customerId = $this->session->get(BilnaStatic::$sessionLoginGuest);
            } else {
                $this->_customerId = uniqid();
                $this->session->set(BilnaStatic::$sessionLoginGuest, $this->_customerId);
            }
            
            $this->_customerKey         = BilnaStatic::$sessionLoginGuestRedis;
            $this->_customerBasketType  = BilnaStatic::$sessionBasketGuest;
        
        }
        $this->_customerStatus          = $this->config->customer->default_status;
    
        //-set customerWebsiteGroup customerPriceGroup
        //$this->_customerWebsiteGroupId  = $this->_getCustomerWebsiteGroupId();
        //$this->_customerPriceGroupId    = $this->_getCustomerPriceGroupId();
        //$this->session->set(BilnaStatic::$sessionGroupWebsite, $this->_customerWebsiteGroupId);
        //$this->session->set(BilnaStatic::$sessionGroupPrice  , $this->_customerPriceGroupId);
        
        //-set customerCartVoucher
        //if ($this->session->has(BilnaStatic::$sessionCartVoucher)) {
        //    $this->_customerBasketVoucherCode = $this->session->get(BilnaStatic::$sessionCartVoucher);
        //}
        //->view->customerBasketVoucherCode = $this->_customerBasketVoucherCode;
        
        $this->view->isCustomerLogin    = $this->_isCustomerLogin;
    }
    
    protected function _getCustomerData() {
        $redisSession = new BilnaRedis(BilnaStatic::$session);
        $customerData = $redisSession->getRedisData($this->_customerKey, $this->_customerId);
        unset ($redisSession);
        if ($customerData) {
            return $customerData;
        }
        
        return false;
    }
    
    protected function _getFirstname($fullname) {
        $nameArr = explode(' ', $fullname);
        $firstname = $nameArr[0];
        $limit = (int) $this->config_apps->userLogin->firstnameLimit;
        
        if (strlen($firstname) > $limit) {
            $firstname = substr($firstname, 0, ($limit - 3)) . '...';
        }
        
        return $firstname;
    }
}
