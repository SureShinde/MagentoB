<?php

use Phalcon\DI\FactoryDefault\CLI as CliDI,
    Phalcon\CLI\Console as ConsoleApp,
    Phalcon\Mvc\Dispatcher;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Logger\Adapter\File as FileAdapter,
	Phalcon\Logger\Multiple as MultipleStream;


define('VERSION', '1.0.0');

chdir(__DIR__);

 //Using the CLI factory default services container
 $di = new CliDI;

 // Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

 /**
  * Register the autoloader and tell it to register the tasks directory
  */
$loader = new \Phalcon\Loader;

$loader->registerNamespaces(array(
	'Api\Customer\Tasks' => __DIR__ . '/../customer/tasks/',
    'Api\Core\Controllers' => __DIR__ . '/../core/controllers/',
    'Api\Core\Libraries' => __DIR__ . '/../core/libraries/',
    'Api\Core\Models' => __DIR__ . '/../core/models/',
    'Api\Customer\Controllers' => __DIR__ . '/../customer/controllers/',
    'Api\Customer\Models' => __DIR__ . '/../customer/models/'
));

$loader->register();

// Load the configuration file (if any)
if(is_readable(APPLICATION_PATH . '/config/config.php')) {
	$config = include APPLICATION_PATH . '/config/config.php';
	$di->set('config', $config);
}

$di['db'] = function () use ($config) {
    $eventsManager = new \Phalcon\Events\Manager;

    $fileAdapterLog = new FileAdapter($config->logger->path.'db_'.date("Ymd").'.log');

    $queryLogger = new \Api\Core\Libraries\Phalcon\Db\Profiler\QueryLogger();
    $queryLogger->setLogger($fileAdapterLog);

    $adapter = new DbAdapter(array(
        "host" => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->dbname
    ));

    $eventsManager->attach('db', $queryLogger);
    //if ($config->debug) {
    if (true) {                
        $adapter->setEventsManager($eventsManager);
    }

    return $adapter;
};

if (! is_dir($config->logger->path)) {
	mkdir($config->logger->path, 0777, TRUE);
}

if(!is_writable($config->logger->path)){
	$old = umask(0);
	@chmod($config->logger->path, 0777);
	umask($old);
}

$di['logger'] = function() use ($config){

	$path = $config->logger->path;

	if (! is_dir($path)) {
		mkdir($path, 0777, TRUE);
	}

	if(!is_writable($path)){
		$old = umask(0);
		@chmod($path, 0777);
		umask($old);
	}

	$logger = new MultipleStream();
	$logger->push(new FileAdapter($path.date("Ymd").'.log'));
	return $logger;
};

$di->set('modelsCache', function() {

	//Cache data for one day by default
	$frontCache = new \Phalcon\Cache\Frontend\Data(array(
		"lifetime" => 60
	));

	if(extension_loaded('apc') && ini_get('apc.enabled'))
	{
		$cache = new Phalcon\Cache\Backend\Apc($frontCache, array(
			'prefix' => 'api-data'
		));

		return $cache;
	}

	if(extension_loaded('xcache'))
	{
		$cache = new Phalcon\Cache\Backend\Xcache($frontCache, array(
			'prefix' => 'api-data'
		));

		return $cache;
	}

	if(extension_loaded('memcache')){
		//Memcached connection settings
		$cache = new \Phalcon\Cache\Backend\Memcache($frontCache, array(
			"host" => "127.0.0.1",
			"port" => "11211"
		));
		return $cache;
	}

	return new Phalcon\Cache\Backend\Memory($frontCache);
});

//Create a console application
$console = new ConsoleApp;
$console->setDI($di);

/**
* Process the console arguments
*/
$arguments = array();
foreach($argv as $k => $arg) {
	if($k == 1) {
		$arguments['task'] = 'Api\Customer\Tasks\\'.ucfirst(Phalcon\Text::camelize($arg));
	} elseif($k == 2) {
		$arguments['action'] = ucfirst(Phalcon\Text::camelize($arg));
	} elseif($k >= 3) {
		$arguments['params'][] = $arg;
	}
}

// define global constants for the current task and action
define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

 try {
     // handle incoming arguments
     $console->handle($arguments);
 }
 catch (\Phalcon\Exception $e) {
     echo $e->getMessage();
     exit(255);
 }