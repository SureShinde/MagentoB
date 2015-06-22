<?php

/**
 * core config for basic config needed for general apps in restfull
 * @author 		Bilna Development <development@bilna.com>
 * @return \Phalcon\Config
 * @package \
 */

if(!defined("INDEXDIR")){
	define("INDEXDIR",__DIR__.'/../..');
}

$di['bilna'] = function(){
	require_once INDEXDIR.'/../config/loader.php';
	return new \Api\BootLoader\BilnaLogger;
};

$masterConfig = include __DIR__ ."/../../../config/config.php";

$config = new \Phalcon\Config([
	'database' => [
		'adapter' => 'Mysql',
		'host' => '54.251.117.157',
		'username' => 'root',
		'password' => 'development38',
		'dbname' => 'bilna_dev_wolverine',
		'options' => [
			\PDO::ATTR_TIMEOUT => 3,
			\PDO::ATTR_PERSISTENT => true,
			//,\PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		]

	],
	'logger' => array(
		'path' => __DIR__.'/../../../logs/core/',
		'name' => 'core'
	),
	'errConst'=>['{{record}}','{{action}}','{{field}}','{{value}}'],
	'timezone' => 'Etc/GMT+0',
	'acl' => __DIR__ . '/acl/',
	'elastica' => [
		'index' => 'logstash-' . date('Y.m.d'),//required
		'type' => 'ioapi',//'ioapi',//required

//		'path'            => null,
//		'url'             => null,
//		'proxy'           => null,
//		'transport'       => null,
		'persistent' => true,
		'timeout'         => '2',
		'connections'     => ['config'=>['timeout'         => '0.01',// 10
										 'host'            => 'log.bilna.com',//'log.bilna.com',
										 'port'            => '9200','curl'=>[CURLOPT_TIMEOUT_MS=>'10']]], // host, port, path, timeout, transport, persistent, timeout, config -> (curl, headers, url)
//		'roundRobin'      => false,
//		'log' => __DIR__ . '/../../../logs/elastica.log',
//		'retryOnConflict' => 0,

	]
]);
$config = (object) array_merge((array) $masterConfig, (array) $config);

return $config;