<?php
/**
 * User: Akbar
 * Date: 6/16/2015
 * Time: 11:47 AM
 */
namespace Frontend\Models;

use Phalcon\DI\Injectable;

abstract class BaseRestModel extends Injectable {

    public function __construct() {
        $this->debug        = $this->di->getFlash();
        //$this->common       = new BilnaCommon();

        $this->clientID     = uniqid();
        $this->clientToken  = (int) microtime(true);
    }

    public function __call ( $name , $arguments ){

        if(isset(static::${$name})){
            //$this->_setData($data);
            $method = 'get';
            if(substr($name,0,3)=='set')$method = 'post';
            elseif(substr($name,0,3)=='remove')$method = 'delete';

            if(isset($arguments[1]) && is_callable($arguments[1]))
                return $this->_request($method,static::${$name},$arguments[0],$arguments[1]);
            else
                $result = $this->_request($method,static::${$name},$arguments[0]);
            if ($result['status']) {
                return $result['response'];
            }

        }

        throw new \Exception('endpoint '.$name.' not defined');
    }

    /**
     * @param string $method
     * @param string $url
     * @param \stdClass $data
     * @param callable $function($content, $url, $ch, $user_data)
     * @param bool $setRedis
     * @return bool|null|\stdClass
     */
    protected function _request($method, $url, array $data, callable $function = null , $setRedis = true) {
        /*die(var_dump($this->_getAuth1('get','http://adminz.bilna.com/api/rest/customers',$data,
            '6bfc2cc160072939681c9f7da872b1ed',$this->config->api->customer_secret,'b89eaccfaadb48a78e012f4033fabdd9','9f922b0b95a953efb4ea714399bda122','HMAC-SHA1','1434687186','21005558396d25ef1e9.34899969','1.0')));
        $method = strtolower($method);*/

        $auths = $this->_getAuth1($method,$url,$data,$this->config->api->customer_key,$this->config->api->customer_secret,
            $this->config->api->request_token,$this->config->api->request_secret);

        array_walk($auths, function(&$item1, $key){$item1 = $key.'="'.$item1.'"';});
        //die(var_dump($auths));
        //var_dump('OAuth '.implode(',',$auths));

        if($function && is_callable($function)){
            //use mcurl
            $curl = $this->getDI()->getShared('mcurl');
            /**
             * @var \Bilna\Libraries\ParallelCurl $curl
             */
            $user_data = [];
            $curl->$method($url,($method=='get'?$data:[]),
                ['User-Agent'=>'PECL-OAuth/1.2.3',
                    'Authorization'=>'OAuth '.implode(',',$auths)],$user_data,$function);

            return null;
        }
        $this->curl->setBaseUri($this->config->api->base_url);
        $start = $this->common->microtime_float();
        //$params = array ('data' => json_encode($this->data));
        $response = $this->curl->$method($url, $data, ['User-Agent'=>'PECL-OAuth/1.2.3',
            'Authorization'=>'OAuth '.implode(',',$auths)]);
        $end = $this->common->microtime_float();
        $this->_apiLogger($this->config->api->base_url . $url, $start, $end, 'api', $data, $response);
        $responseData = json_decode($response->body, true);
        return $responseData;
    }

    protected $_nonce_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.";

    protected function _getNonce($length=5)
    {
        $result = '';
        $cLength = strlen($this->_nonce_chars);
        for ($i=0; $i < $length; $i++)
        {
            $rnum = rand(0,$cLength - 1);
            $result .= substr($this->_nonce_chars,$rnum,1);
        }
        //$this->_parameters['oauth_nonce'] = $result;
        return $result;
    }

    protected function _urlencode_rfc3986($string)
    {
        if ($string === 0) { return 0; }
        if ($string == '0') { return '0'; }
        if (strlen($string) == 0) { return ''; }
        if (is_array($string)) {
            throw new \Exception('Array passed to _urlencode_rfc3986');
        }
        $string = urlencode($string);
        //FIX: urlencode of ~ and '+'
        $string = str_replace(
            Array('%7E','+'  ), // Replace these
            Array('~',  '%20'), // with these
            $string);
        return $string;
    }

    protected function _getAuth1($method,$url,$user_data,$oauth_consumer_key,$oauth_consumer_secret,$oauth_token,$oauth_secret,$oauth_signature_method = 'HMAC-SHA1',$oauth_timestamp= null,$oauth_nonce=null,$oauth_version='1.0'){

        if(!$oauth_timestamp)$oauth_timestamp = time();
        if(!$oauth_nonce)$oauth_nonce = $this->_getNonce(28);

        // BUILD SIGNATURE
        $params = compact("oauth_consumer_key","oauth_nonce","oauth_signature_method","oauth_timestamp","oauth_token","oauth_version");
        $params = array_merge($user_data,$params);
        // encode params keys, values, join and then sort.
        foreach ($params as $k => $v) {$pairs[] = $this->_urlencode_rfc3986($k).'='.$this->_urlencode_rfc3986($v);}
        // convert params to string
        $concatenatedParams = implode('&', $pairs);

        // form base string (first key)
        $baseString = strtoupper($method).'&'.$this->_urlencode_rfc3986($url).'&'.$this->_urlencode_rfc3986($concatenatedParams);
        // var_dump($baseString);
        // form secret (second key)
        $secret = $this->_urlencode_rfc3986($oauth_consumer_secret).'&'.$this->_urlencode_rfc3986($oauth_secret);
        // make signature and append to params
        $oauth_signature = $this->_urlencode_rfc3986(base64_encode(hash_hmac('sha1', $baseString, $secret, TRUE)));

        return compact("oauth_consumer_key","oauth_signature_method","oauth_nonce","oauth_timestamp","oauth_version","oauth_token","oauth_signature");
    }

    protected function _apiLogger($url, $start, $end, $source, $request, $response) {
        $message = array (
            'time' => array (
                'start'     => $start,
                'end'       => $end,
                'totaltime' => ($end - $start),
            ),
            'source'    => $source,
            'endpoint'  => $url,
            'request'   => $request,
            'response'  => $response,
        );

        $this->logger->info(json_encode($message));
    }
}