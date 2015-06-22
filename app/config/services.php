<?php

/**
 * Services are globally registered in this file
 */

use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Collection\Manager as CollectionManager;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Multiple as MultipleStream;
use Phalcon\Mvc\View;
use \Bilna\Phalcon\Http\Client\Request as ClientRequest;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as Session;
use Bilna\Phalcon\Session\Adapter\Redis as SessionRedis;
use Phalcon\Http\Request as Request;
use Phalcon\Mvc\Dispatcher as Dispatcher;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault;

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di['url'] = function () use ($config){

    $url = new UrlResolver;
    
    $url->setBaseUri($config->baseUri);
    $url->setStaticBaseUri($config->baseStaticUri);
//     $url->setMediaUri($config->media->url);

    return $url;
};

/**
 * set config
 */
$di->set('config', function () {
    return require __DIR__.'/config.php';
});

$di->set('config_apps', function () {
    return require __DIR__.'/config_apps.php';
});

/**
 * Setting up the view component
 */
$di->set('view', function() use ($config, $di) {
    $config_apps = require __DIR__.'/config_apps.php';
    $cacheDir = $config_apps->application->cacheDir;

    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }

    if (!is_writable($cacheDir)) {
        $old = umask(0);
        @chmod($cacheDir, 0777);
        umask($old);
    }

    $evManager = $di->getShared('eventsManager');
    $evManager->attach('view:beforeRender', function($event, $view) use($di) {
        $mcurl = $di->getShared('mcurl');
        if(count($mcurl->outstanding_requests))
            $mcurl->finishAllRequests();
        return true;
    });

    $view = new \Phalcon\Mvc\View;
    $view->setEventsManager($evManager);
    $view->setViewsDir($config_apps->application->viewsDir);
    $view->registerEngines(array (
        '.volt' => function($view, $di) use ($config, $config_apps) {
            $volt = new VoltEngine($view, $di);
            $volt->setOptions(array (
                'compiledPath' => $config_apps->application->cacheDir,
                'compiledSeparator' => '_',
                'stat' => true,
                'compileAlways' => ($config->environment == 'development') ? true : false,
            ));

            $compiler = $volt->getCompiler();
            $compiler->addFunction('widget', function($resolvedArgs) {
                return '\Bilna\Libraries\WidgetManager::get('.$resolvedArgs.')->getContent()';
            });

            return $volt;
        },
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
    ));

    return $view;
}, true);

$di['modelsMetadata'] = function () {

	if (extension_loaded('apc') && ini_get('apc.enabled')) {
		$metaData = new Phalcon\Mvc\Model\Metadata\Apc([
			'prefix' => 'bilna_3',
			'lifetime' => 300
		]);

		return $metaData;
	}

	if (extension_loaded('xcache')) {
		$metaData = new Phalcon\Mvc\Model\Metadata\Xcache([
			'prefix' => 'bilna_3',
			'lifetime' => 300
		]);

		return $metaData;
	}

	// Create a meta-data manager with APC
	$metaData = new \Phalcon\Mvc\Model\MetaData\Memory([
		"lifetime" => 300,
		"prefix" => "bilna_3"
	]);
	$metaData = new \Phalcon\Mvc\Model\Metadata\Files([
		'metaDataDir' => realpath(__DIR__.'\..\cache\\').'\\',
	]);

//	die(realpath(__DIR__.'\..\cache\\').'\\');
	// Create a meta-data manager with APC
//	$metaData = new \Phalcon\Mvc\Model\MetaData\Files(array(
//		'metaDataDir' => sys_get_temp_dir()."/"
//	));
	return $metaData;
};

$di->set('modelsCache', function () {

	//Cache data for one day by default
	$frontCache = new \Phalcon\Cache\Frontend\Data([
		"lifetime" => 60
	]);

//	$cache = new Phalcon\Cache\Backend\Mongo($frontCache, array(
//		'server' => "mongodb://localhost",
//		'db' => 'caches',
//		'collection' => 'images'
//	));
//
//	return $cache;

	if (extension_loaded('apc') && ini_get('apc.enabled')) {
		$cache = new Phalcon\Cache\Backend\Apc($frontCache, [
			'prefix' => 'api-data'
		]);

		return $cache;
	}

	if (extension_loaded('xcache')) {
		$cache = new Phalcon\Cache\Backend\Xcache($frontCache, [
			'prefix' => 'api-data'
		]);

		return $cache;
	}

	if (extension_loaded('memcache')) {
		//Memcached connection settings
		$cache = new \Phalcon\Cache\Backend\Memcache($frontCache, [
			"host" => "127.0.0.1",
			"port" => "11211"
		]);
		return $cache;
	}

	return new Phalcon\Cache\Backend\Memory($frontCache);
});

$di->set('modelsManager', function () {
    return new Phalcon\Mvc\Model\Manager;
});

$di->set('mongo', function() {
	if(!class_exists('MongoClient'))return null;
	$mongo = new MongoClient("mongodb://wolverine:development38@mongo.bilna.org:27017/bilna_db_changes_logger",
		["connectTimeoutMS"=> 10,
		 "readPreference"  => MongoClient::RP_NEAREST,
//		 "readPreferenceTags"=>MongoClient::RP_SECONDARY_PREFERRED,
//		 "socketTimeoutMS"=>850
		]);// $mongo = new MongoClient("mongodb:///tmp/mongodb-27017.sock,localhost:27017");

	return $mongo->selectDB("bilna_db_changes_logger");
}, true);

$di->set('collectionManager', function () {

//	$eventsManager = new EventsManager();

	// Attach an anonymous function as a listener for "model" events
//	$eventsManager->attach('collection', function($event, $model) {
//		if (get_class($model) == 'Robots') {
//			if ($event->getType() == 'beforeSave') {
//				if ($model->name == 'CustomerCollections') {
//					echo "Scooby Doo isn't a robot!";
//					return false;
//				}
//			}
//		}
//		return true;
//	});

	// Setting a default EventsManager
	$modelsManager = new CollectionManager;
//	$modelsManager->setEventsManager($eventsManager);
	return $modelsManager;

}, true);


/**
 * Session in Redis
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function() use ($config) {
    $session = new SessionRedis(array (
        'path' => sprintf("tcp://%s:%d?weight=1", $config->cache->redis->ip, $config->cache->redis->port),
        //'name' => 'BILNA_SESSION',
        'lifetime' => isset ($config->session->lifetime) ? $config->session->lifetime : 1440,
        //'cookie_lifetime' => '',
        //'cookie_secure' => true,
    ));

    if (!$session->isStarted()) {
        $session->start();
    }

    return $session;
    $session = new SessionAdapter();
    $session->start();

    return $session;
});

$di->set('flash', function() {
    return new \Phalcon\Flash\Session();
});



/**
 * Registering a router
 */
$di['router'] = function () {
    return require __DIR__ . '/routes.php';
    
    /*
    $router = new Router;

    $router//->setDefaultModule('')
    ->setDefaultController('index')
        ->setDefaultAction('index');;
    //$router->setDefaultNamespace("Api\\Core\\Controller\\");

    if(defined("VERSION") && VERSION)
    {
        $version = new Router\Group(['module' => 'dashboard',
            'controller' => 'dashboard']);
        $version->setPrefix('/'.VERSION); //use VERSION as prefix
		die(VERSION);
    }
    else
        $version = $router;

    $version->add('/:module/:controller/:action',
        [
            'module' => 1,
            'controller' => 2,
            'action' => 3
        ])->convert('action', function ($action) {
        return lcfirst(\Phalcon\Text::camelize($action));
    })->convert('module', function ($module) {
        return lcfirst(\Phalcon\Text::camelize($module));
    })->setName('default');

    $version->add('',[	'module' => 'dashboard',
                        'controller' => 'dashboard',
                        'action' => 'index'
    ])->setName('home');

    $version->add('/:controller/:action',
        [
            'module' => 1,
            'controller' => 1,
            'action' => 2
        ])->convert('action', function ($action) {
        return \Phalcon\Text::camelize($action);
    })->convert('module', function ($module) {
        return lcfirst(\Phalcon\Text::camelize($module));
    })->setName('default_indexing');

    $version->add('/dashboard/:controller',
        [
            'module' => 'dashboard',
            'controller' => 1,
            'action' => 'index'
        ])->setName('dashboard_indexing');


    $version->addPost('/dashboard/:controller', [
        'module' => 'dashboard',
        'controller' => 1,
        'action' => 'ajax'
    ])->beforeMatch(function ($uri, $route) {
        //Check if the request was made with Ajax
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
            return false;
        }
        return true;
    })->setName('dashboard_indexing_ajax');


    $version->addPost('/dashboard/:controller/:action', [
        'module' => 'dashboard',
        'controller' => 1,
        'action' => 2
    ])->convert('action', function ($action) {
        return lcfirst(\Phalcon\Text::camelize($action)) . 'Ajax';
    })->beforeMatch(function ($uri, $route) {
        //Check if the request was made with Ajax
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
            return false;
        }
        return true;
    })->setName('dashboard_ajax');

    
    //$version->add('/login',
    //    [
    //        'module' => 'dashboard',
    //        'controller' => 'login',
    //        'action' => 'index'
    //    ])->setName('dashboard_login');

    //$version->addPost('/login',
    //    [
    //        'module' => 'dashboard',
    //        'controller' => 'login',
    //        'action' => 'post'
    //    ])->setName('dashboard_login_post');

    //$version->add('/logout',
    //    [
    //        'module' => 'dashboard',
    //        'controller' => 'login',
    //        'action' => 'logout'
    //    ])->setName('dashboard_logout');

    //	$router->notFound(array(
    //		'module'	=> 'core',
    //		"controller" => "index",
    //		"action" => "show404"
    //	));
    //if(VERSION)
    //    $router->mount($version);
    //
    //return $router;
     * 
     */
};

$di['logger'] = function () {
    $config_apps = require __DIR__.'/config_apps.php';

    $path = $config_apps->logger->path;

    if (!is_dir($path)) {
        mkdir($path, 0777, TRUE);
    }

    if (!is_writable($path)) {
        $old = umask(0);
        @chmod($path, 0777);
        umask($old);
    }

    $logger = new MultipleStream;
    $logger->push(new FileAdapter($path . date("Ymd") . '.log'));

    return $logger;
};

/**
 * setting redis cache
 */
 $di->set('redis', function() use ($config) {
    //Connect to redis
    $redis = new Redis();
    $redis->connect($config->cache->redis->ip, $config->cache->redis->port);
    
    if ($config->cache->redis->auth) {
        $redis->auth($config->cache->redis->auth);
    }
    
    return $redis;
});

/**
 * Setting up the view component
 */
//$di->setShared('cookies', function() {
//    $cookies = new \Phalcon\Http\Response\Cookies;
//    $cookies->useEncryption(true);
//
//    return $cookies;
//});


/**
 * setting dispatcher
 */
$di->set('dispatcher', function() use ($di) {
    $evManager = $di->getShared('eventsManager');
    $evManager->attach('dispatch:beforeException', function($event, \Phalcon\Mvc\Dispatcher $dispatcher, \Exception $exception) {
        switch ($exception->getCode()) {
            case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
            case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                $dispatcher->forward(array (
                    'controller' => 'index',
                    'action' => 'route404',
                ));

                return false;
        }
    });

    $dispatcher = new Dispatcher;
    $dispatcher->setDefaultNamespace('Frontend\Controllers');
    $dispatcher->setEventsManager($evManager);

    return $dispatcher;
}, true);

$di->setShared('mcurl', function(){
    return new Bilna\Libraries\ParallelCurl(10,[]);
});

/**
 * setting CURL
 */
$di->set('curl', function () {
    return ClientRequest::getProvider(); // get available provider Curl or Stream
});
