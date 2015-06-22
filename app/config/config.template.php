<?php
/**
 * Description of config
 *
 * @author Bilna Development Team <development@bilna.com>
 */

use Phalcon\Config;

unset($config_ini);
if(is_file(__DIR__.'/config.ini') && is_writable(__DIR__.'/config.ini'))
{
    $config_ini = new Phalcon\Config\Adapter\Ini("config.ini");
}else{
    if(is_file(__DIR__.'/config.ini') && !is_writable(__DIR__.'/config.ini'))
        throw new Exception(__DIR__.'/config.ini not writeable');
    elseif(!class_exists('WriteIniFile\WriteIniFile')){
        throw new Exception('vendor composer Magicalex/WriteIniFile Not exist');
    }
}

$config = new Config(array (
    'version' => '0.0.2',
    'baseUri' => 'http://bilna.org/',
    'baseStaticUri' => 'http://bilna.org/', //Static resources go through a CDN
    'media' => array (
        'url' => 'http://im.bilna.org/', //Static resources go through a CDN
        'content' => array (
            'product' => array (
                'url' => 'products/',
                'original' => 'original/',
                'small' => 'small/',
                'large' => 'large/',
                'base' => 'base/',
            ),
        ),
    ),
    'session' => array (
        'lifetime' => 1440,
    ),
    'cache' => array (
        'redis' => array (
            'enabled' => array (
                'api' => true,
                'widget' => true,
                'lang' => true,
                'session' => true,
                'routes' => true,
            ),
            'ip' => 'redis.bilna.org',
            'port' => 6379,
            'auth' => false,
        ),
    ),
    'environment' => 'development', // development/production
    'developmentIpAddress' => '116.254.102.193',
    'debug' => false,
    'api' => array (
        'base_url' => 'http://adminz.bilna.com',
        'customer_key' => (isset($config_ini) && $config_ini->api->customer_key)?$config_ini->api->customer_key:'6bfc2cc160072939681c9f7da872b1ed',
        'customer_secret' => (isset($config_ini) && $config_ini->api->customer_secret)?$config_ini->api->customer_secret:'a4f266f95957cf8aa732b192397fcde0',

        'url_init' => '/oauth/initiate',
        'url_admin_auth' => '/g10rdano/oauth_authorize',
        'url_token' => '/oauth/token',
        'url_api' => '/api/rest',

        'request_token' => (isset($config_ini) && $config_ini->api->request_token)?$config_ini->api->request_token:null,
        'request_secret' => (isset($config_ini) && $config_ini->api->request_secret)?$config_ini->api->request_secret:null,
    ),
    'translation' => array (
        'data' => array (
            'ID' => 'IDN',
            'EN' => 'ENG',
        ),
        'default' => 'ID',
    ),
    'ftp' => array(
      'host'        => 'www.bilna.org',
      'username'    => 'webdev',
      'password'    => 'development38',
      'port'        => '21',
      'path_destination' => '',//'/home/webdev/bilna.org/backend/public/img/',
      'path_source' => 'img/tmp/'
    ),
    
    'customer' => array (
        'default_status' => 0, //- 0->pending, 1->active, 2->inactive
        'gender' => array (
            1 => 'Male',
            2 => 'Female',
            3 => 'Other',
        ),
        'gender_default' => 1,
    ),
    'social' => array (
        'facebook' => array (
            'app_id' => '1542558455985531',//'1542558455985531',
            'app_secret' => 'ef0e9d282165ad74b3bc0363c52d1775'//'ef0e9d282165ad74b3bc0363c52d1775'
        ),
        'twitter' => array (
            'app_id' => 'ctoUb4raw1FMVFgCRoyNvQwtG',//'orbou1VS309fNj57JdSmDX78g',
            'app_secret' => 'A2bmcj1MQEwU3ftcV0T5KG3qaX66jkTWygC8MDpAY9Vc1II528',//'r9P2cUhzb4K1OXed0hZw4A00u5reAGpRHUIfHsAkw2DA309pnY',
            'app_callback' => 'login-check?method=twitter'
        ),
        'google' => array (
            'app_id'        => '31500040120-mht5lhsn0non55jaob529iv27cst7g2d.apps.googleusercontent.com',//'695128777120-4ejuvohft3lsooiua1i1v1hcb8vmui63.apps.googleusercontent.com',
            'app_secret'    => 'SnGihItIzuxmUJiwbwLVR9KJ',//'lziNH0h6J0-nYS7__lM7gn4r',
            'app_key'       => 'AIzaSyAR3RtcMCk50oZEqo945MM9PHMbGv9kQhw',//'AIzaSyCWHzDsTUkhdUbmKI9c1NJ-XJ0Z4l_XO10',
            'app_callback'  => 'login-check?method=google'
        ),
    ),
    'mail' => array (
        'fromName'  => 'Bilna Dev Team',
        'fromEmail' => 'mirasz@bilna.com',
        'smtp'      => array(
            'server'     => 'email-smtp.us-east-1.amazonaws.com',
            'access_key' => 'AKIAJYFT3LWSXMXTSBIQ',
            'secret_key' => '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',
            'port'       => '587',
            'security'   => 'tsl',
            'username'   => 'AKIAIFHF4WJYKCVMM2UQ',
            'password'   => 'AkWyw1Pt0pY3dnAJLn7FUDMVZ4yS4falcMPDBlvLOh9N'
        ),
    ),
    'analytics' => array (
        'gtm' => array (
            'enabled' => true,
            'key' => 'GTM-TDK22V',
            'content' => "<noscript><iframe src=\"//www.googletagmanager.com/ns.html?id=GTM-TDK22V\"
height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TDK22V');</script>",
        ),
    ),
    'recaptcha' => array(
        'enabled' => true,
        'app_id'        => '6LfnpwQTAAAAAN00uEthRnXnBWV0ow92s0Ythh8B',
        'app_secret'    => '6LfnpwQTAAAAAJZdXZT9bCwU1nROLco6hM9cCCx4',
        'app_callback'  => 'https://www.google.com/recaptcha/api/siteverify',
    ),
    'salt' => 'vtVaR-5SXb',
    'sendEmailDirect' => true,
    'formatDate' => array (
        'js' => 'dd-mm-yy',
        'php' => 'Y-m-d',
        'mysql' => 'Y-m-d',
    ),
));

return $config;
