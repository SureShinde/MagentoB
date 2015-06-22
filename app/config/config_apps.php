<?php
/**
 * Description of config_apps
 *
 * @author Bilna Development Team <development@bilna.com>
 */

use Phalcon\Config;

$config_apps = new Config(array (
    'application' => array (
        'controllersDir' => __DIR__ . '/../../app/controllers/',
        'modelsDir' => __DIR__ . '/../../app/models/',
        'viewsDir' => __DIR__ . '/../../app/views/',
        'pluginsDir' => __DIR__ . '/../../app/plugins/',
        'libraryDir' => __DIR__ . '/../../app/library/',
        'incubatorDir' => __DIR__ . '/../../app/library/Phalcon/',
        'bilnaDir' => __DIR__.'/../../app/library/Bilna/',
        'cacheDir' => __DIR__ . '/../../var/cache/',
        'addressDir' => __DIR__ . '/../../var/json/',
        'facebookDir' => __DIR__ . '/../../vendor/facebook/php-sdk-v4/src/Facebook',
        'twitterDir' => __DIR__ . '/../../app/library/Bilna/Twitter/',
        'pubsubhubbubDir' => __DIR__.'/../../app/library/Bilna/Pubsubhubbub/',
        'widgetDir' => __DIR__ . '/../../app/widgets/',
        'googleDir' => __DIR__ . '/../../vendor/google/apiclient/src/Google/'
    ),
    'logger' => array (
        'path' => __DIR__ . '/../../var/logs/',
        'name' => 'frontend',
    ),
    'assets' => array (
        'default_css' => array (
            'bilna/reset',
            'thirdparty/font-awesome',
            'thirdparty/bootstrap',
            'bilna/bilna',
            'bilna/header',
            'bilna/footer',
            'bilna/stylesheet',
            //'bilna/jquery.rating',
            'thirdparty/jquery-ui',
            'bilna/megamenu',
            'bilna/base_var',
        ),
        'default_js' => array (
            'header' => array (
                'thirdparty/less.min',
                'thirdparty/jquery-2.1.1.min',
                'thirdparty/bootstrap.min',
                'thirdparty/jquery-mobile',
                'thirdparty/jquery.nicescroll.min',
                'thirdparty/jquery-ui',
                'thirdparty/jquery.bxslider',
                'thirdparty/jquery-lazyload',
                'thirdparty/echo',
                'bilna/bxslider',
                'bilna/cart',
                'bilna/filter',
                'bilna/search',
                'bilna/validation',
            ),
            'footer' => array (
                'bilna/bilna',
                'bilna/register',
                'bilna/customer',
            ),
        ),
        'exclude_js' => array (
            'thirdparty/less.min'
        ),
        'files' => array (
            'jpg',
            'jpeg',
            'bmp',
            'png',
            'css',
            'js',
            'less',
            'ico',
        ),
    ),
    'limitation' => array(
        'pagination' => 30,
        'image'     => 12,
        'showDataPerPage' => array(30,45,60,'all'),
    ),
    'userLogin' => array (
        'firstnameLimit' => 12,
    ),
));

return $config_apps;
