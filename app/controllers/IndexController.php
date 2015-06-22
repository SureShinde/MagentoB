<?php

/*
 * @author Bilna Development <development@bilna.com> 
 */

namespace Frontend\Controllers;

use Frontend\Models\Customers;
use Phalcon\Flash\Direct,
    Phalcon\Flash\Session,
    Phalcon\Acl;

use \Frontend\Libraries\BackendValidator,
    \Frontend\Libraries\Authentication;

use \OAuth\Common\Storage\Session as OAuthSession;
use \OAuth\Common\Consumer\Credentials;
use \OAuth\Common\Http\Uri\UriFactory;
use \OAuth\ServiceFactory;
use \Bilna\Libraries\MagentoOAuth as Magento;

class IndexController extends CoreController
{

    protected $_css = array ('bilna/product_detail', 'bilna/homepage');
    
    public function indexAction() {
        $this->view->title = 'Homepage';
    }

    public function AuthAction(){

        $customer = new Customers();
        $customer->getData(['limit'=>1],function($content, $url, $ch, $user_data){
            echo $content;
        });

        return;

        $callbackUrl = "http://frontend.bilna.dev/index/auth";
        $temporaryCredentialsRequestUrl = "http://adminz.bilna.com/oauth/initiate?oauth_callback=" . urlencode($callbackUrl);
        $adminAuthorizationUrl = 'http://adminz.bilna.com/g10rdano/oauth_authorize';
        $accessTokenRequestUrl = 'http://adminz.bilna.com/oauth/token';
        $apiUrl = 'http://adminz.bilna.com/api/rest';
        $consumerKey = '6bfc2cc160072939681c9f7da872b1ed';
        $consumerSecret = 'a4f266f95957cf8aa732b192397fcde0';
        /*$_SESSION['token'] = '166f989639d0c77b195bbe4bdf218bda';
        $_SESSION['secret'] = '32930d0e1dfb46490325dacf7aa451f7';*/

        session_start();
        if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
            $_SESSION['state'] = 0;
        }elseif(!isset($_SESSION['state'])){
            $_SESSION['state'] = 0;
        }
        try {
            $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
            $oauthClient = new \OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
            $oauthClient->enableDebug();
            if (!isset($_GET['oauth_token'])/* && !$_SESSION['state'] && !isset($_SESSION['token'], $_SESSION['secret'])*/) {
                //unset($_COOKIES);
                //echo get_class($oauthClient);
                die($temporaryCredentialsRequestUrl);
                $requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
                $_SESSION['secret'] = $requestToken['oauth_token_secret'];
                $_SESSION['state'] = 1;
                header('Location: ' . $adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']);
                exit;
            } else if ($_SESSION['state'] == 1) {die('ok');
                $oauthClient->setToken($_GET['oauth_token'], $_SESSION['secret']);
                $accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);
                $_SESSION['state'] = 2;
                $_SESSION['token'] = $accessToken['oauth_token'];
                $_SESSION['secret'] = $accessToken['oauth_token_secret'];
                header('Location: ' . $callbackUrl);
                exit;
            } else {
                //die(var_dump([$_SESSION['token'], $_SESSION['secret']]));
                $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
                $resourceUrl = "$apiUrl/customers";
                $oauthClient->fetch($resourceUrl);
                $productsList = json_decode($oauthClient->getLastResponse());
                print_r($productsList);
            }
        } catch (OAuthException $e) {
            print_r($e);
        }

        die();



        $applicationUrl     = 'http://adminz.bilna.com';
        $consumerKey        = '6bfc2cc160072939681c9f7da872b1ed';
        $consumerSecret     = 'a4f266f95957cf8aa732b192397fcde0';
        $requestToken       = '37817a7fb889bff1de81985211b40c2d';
        $requestSecretToken = 'd4fe31fb5eba6958129a602498f110ef';

        /**
         * Setup service
         */
        $storage        = new OAuthSession();
        if(isset($requestToken,$requestSecretToken))
        $storage->storeAccessToken($storage->service(), $token);

        $uriFactory     = new UriFactory();
        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('Magento', 'Bilna\Libraries\MagentoOAuth');
        $serviceFactory->setHttpClient(new \OAuth\Common\Http\Client\CurlClient());
        $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
        $currentUri->setQuery('');
        $baseUri = $uriFactory->createFromAbsolute($applicationUrl);
        $credentials = new Credentials(
            $consumerKey,
            $consumerSecret,
            $currentUri->getAbsoluteUri()
        );
        $magentoService = $serviceFactory->createService('Magento', $credentials, $storage, array(), $baseUri);

        $magentoService->setAuthorizationEndpoint(Magento::AUTHORIZATION_ENDPOINT_ADMIN);
        /**
         * OAuth logic
         */
// +++++++++++++++++++++++++ //
// AUTHENTICATION CANCELLED  //
// +++++++++++++++++++++++++ //
        if(isset($_GET['rejected'])) {
            echo '<p>OAuth authentication was cancelled.</p>';
        }
// +++++++++++++++++++++++++ //
// AUTHENTICATE WITH MAGENTO //
// +++++++++++++++++++++++++ //
        elseif(isset($_GET['authenticate'])) {
            // extra request needed for oauth1 to request a request token :-)
            $token     = $magentoService->requestRequestToken();
            $url     = $magentoService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));

            header('Location: ' . $url);
        }
// +++++++++++++++++++++++++++++++++++++ //
// GET ACCESS TOKEN AFTER AUTHENTICATION //
// +++++++++++++++++++++++++++++++++++++ //
        elseif(!empty($_GET['oauth_token']) || isset($requestToken,$requestSecretToken)) {
            //die(var_dump($_SESSION));
            $token = $storage->retrieveAccessToken('Magento');
            //die(var_dump($token));
            if(!$token->getRequestToken() || !$token->getRequestTokenSecret())
            // This was a callback request from twitter, get the token
            $token = $magentoService->requestAccessToken(
                $_GET['oauth_token'],
                $_GET['oauth_verifier'],
                $token->getRequestTokenSecret()
            );
            //var_dump($token);
            // Send a request now that we have access token
            $result = $magentoService->request('/api/rest/products?limit=2', 'GET', null, array('Accept' => 'application/json', 'Content-Type' => 'application/json'));
            echo 'result: <pre>' . print_r(json_decode($result), true) . '</pre>';
        }
// +++++++ //
// DEFAULT //
// +++++++ //
        else {
            $url = $currentUri->getRelativeUri() . '?authenticate=true';

            echo '<a href="' . $url . '" title="Authenticate">Authenticate!</a>';
        }
        die();
    }

	public function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
	
	public function route404Action()
	{
		return $this->view->pick("route404");
	}

}
