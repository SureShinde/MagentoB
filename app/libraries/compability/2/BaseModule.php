<?php
namespace Frontend\Compability;
use Phalcon\Mvc\ModuleDefinitionInterface;

/**
 * User: Akbar
 * Date: 6/3/2015
 * Time: 10:41 AM
 */

abstract class BaseModule implements ModuleDefinitionInterface {
	public function registerAutoloaders(\Phalcon\DiInterface $dependencyInjector = NULL)
	{

	}
	public function registerServices(\Phalcon\DiInterface $di)
	{
		$this->registerServicesAdapt($di);
	}
	public function registerServicesAdapt(\Phalcon\DiInterface $di){

	}
	protected function __mkdirLog($path)
	{
		if (! is_dir($path)) {
			mkdir($path, 0777, TRUE);
		}

		if(!is_writable($path)){
			$old = umask(0);
			@chmod($path, 0777);
			umask($old);
		}
		return is_writable($path);
	}
}