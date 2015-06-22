<?php
/**
 * Core Module
 */
namespace Frontend\Customer;

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Loader;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Multiple as MultipleStream;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

class Module extends \Frontend\Compability\BaseModule
{

	/**
	 * Registers the module auto-loader
	 */
	public function registerAutoloaders(\Phalcon\DiInterface $dependencyInjector = NULL)
	{
		$loader = new Loader();

		$loader->registerNamespaces([
			'Frontend\Customer\Controllers' => __DIR__ . '/controllers/',
			'Frontend\Customer\Models' => __DIR__ . '/models/',
			'Frontend\Customer\Libraries' => __DIR__ . '/libraries/',
		]);

		$loader->register();
	}

	/**
	 * Registers the module-only services
	 *
	 * @param \Phalcon\DI $di
	 */
	public function registerServicesAdapt(\Phalcon\DiInterface $di)
	{
		/**
		 * Read configuration
		 */
		$config = include __DIR__ . "/config/config.php";

		/**
		 * Registering config
		 */
		$di['config'] = function () use ($config) {
			return $config;
		};

		//Registering a dispatcher
		$di->set('dispatcher', function() {
			$eventsManager = new \Phalcon\Events\Manager;

			$eventsManager->attach("dispatch:beforeException",

				/**
				 * @param \ErrorException|\Phalcon\Mvc\Dispatcher\Exception $exception
				 */
				function(\Phalcon\Events\Event $event, \Phalcon\Mvc\Dispatcher $dispatcher,\Exception $exception) {

				if ($exception->getCode() == \Phalcon\Mvc\Dispatcher::EXCEPTION_ACTION_NOT_FOUND){
					$dispatcher->forward(
						array(
							'controller' => $dispatcher->getControllerName(),
							'action'     => 'show404',
						)
					);
					return false;
				}
			});
			$dispatcher = new Dispatcher;
			$dispatcher->setDefaultNamespace('Frontend\Customer\Controllers');
			$dispatcher->setEventsManager($eventsManager);
			return $dispatcher;
		});

		/**
		 * Setting up the view component
		 */
		$di['view'] = function () {
			$view = new View();
			$view->setViewsDir(__DIR__ . '/views/');
            $view->registerEngines(array (
//                 '.volt' => function($view, $di) use ($config, $config_apps) {
                '.volt' => function($view, $di) {
                    $volt = new VoltEngine($view, $di);
                    $volt->setOptions(array (
//                         'compiledPath' => $config_apps->application->cacheDir,
                        'compiledPath' => __DIR__ . '../../../../var/cache/',
                        'compiledSeparator' => '_',
                        'stat' => true,
//                         'compileAlways' => ($config->environment == 'development') ? true : false,
                        'compileAlways' => true,
                    ));
        
                    $compiler = $volt->getCompiler();
                    $compiler->addFunction('widget', function($resolvedArgs) {
                        return '\Bilna\Libraries\WidgetManager::get('.$resolvedArgs.')->getContent()';
                    });
        
                    return $volt;
                }
            ));

			return $view;
		};
	}
}
