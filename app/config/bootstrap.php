<?php
//$config = include __DIR__ . "/config.php";
//include __DIR__ . "/loader.php";

//$di = new \Phalcon\DI\FactoryDefault();

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Loader;
use Phalcon\Logger\Adapter\File as FileAdapter;

$loader = new Loader();

$loader->registerNamespaces(array(
	'Api\Brand\Models' => __DIR__ . '/../apps/brand/models/',
	'Api\Brand\Controllers' => __DIR__ . '/../apps/brand/controllers/',

	'Api\Category\Models' => __DIR__ . '/../apps/category/models/',
	'Api\Product\Models' => __DIR__ . '/../apps/product/models/',
	'Api\Core\Libraries' => __DIR__ . '/../apps/core/libraries/',
	'Api\Core\Models' => __DIR__ . '/../apps/core/models/',
))->register();

include __DIR__ . "/services.php";
$config = new \Phalcon\Config(array(
	'database' => array(
		'adapter' => 'Mysql',
		'host' => '192.168.2.7',
		'username' => 'root',
		'password' => 'abcd1234mysql',
		'dbname' => 'new_bilna_dev_wolverine',
	),
	'logger' => array(
		'path' => __DIR__ . '/../logs/codecept/',
		'name' => 'codecept'
	),
));

$di['db'] = function () use ($config) {
	$eventsManager = new \Phalcon\Events\Manager;

	$fileAdapterLog = new FileAdapter($config->logger->path . 'db_' . date("Ymd") . '.log');

	$queryLogger = new \Api\Core\Libraries\Phalcon\Db\Profiler\QueryLogger;
	$queryLogger->setLogger($fileAdapterLog);

	$adapter = new DbAdapter(array(
		"host" => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname" => $config->database->dbname
	));

	/*$eventsManager->attach('db', function($event, $adapter) use ($logging) {
		if ($event->getType() == 'beforeQuery') {
			$logging->log($adapter->getSQLStatement());
		}
		if ($event->getType() == 'afterQuery') {
			$logging->log($adapter->getSQLStatement(), \Phalcon\Logger::INFO);
		}
	});*/
	$eventsManager->attach('db', $queryLogger);
	//if ($config->debug) {
	if (true) {
		$adapter->setEventsManager($eventsManager);
	}

	return $adapter;
};


//die(var_export($di,1));
$application = new \Phalcon\Mvc\Application($di);
require __DIR__ . '/modules.php';
return $application;