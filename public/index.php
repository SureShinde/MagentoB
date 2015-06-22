<?php
/**
 * User: Akbar
 * Date: 6/15/2015
 * Time: 5:25 PM
 */

use Bilna\Libraries\BilnaLogger;
use Phalcon\Mvc\Application;

list(,$segment1) = explode('/',$_SERVER['REQUEST_URI']);
if(preg_match('@^v?[0-9\.]+$@',$segment1) && is_dir(__DIR__ . '/../'.$segment1)){
    define("VERSION",$segment1);
}else{
    define("VERSION",'');
}


try {
    ini_set('display_errors', 1);
    /**
     * Read auto-loader
     */
    include_once __DIR__ . '/..'.(VERSION?'/'.VERSION:'').'/app/config/loader.php';
    BilnaLogger::start();

    /**
     * Read autoload composer.
     */
    if(is_file(__DIR__.'/../vendor/autoload.php'))
    require_once __DIR__.'/../vendor/autoload.php';

    /**
     * Read the configuration
     */
    require_once __DIR__ . "/../app/config/config.php";
    //require_once __DIR__ . "/../app/config/config_apps.php";

//  /R/O/O/T/branchName/public/index.php
//  /R/O/O/T/branchName/index.php
//  How to get branchName
    define("INDEXDIR",__DIR__);
    $webdir = realpath($_SERVER["DOCUMENT_ROOT"].'/..');

    if(in_array($dir = basename(INDEXDIR),['public','public_html']))
    {
        $webdir = str_replace(DIRECTORY_SEPARATOR.$dir,'',$webdir);
        $indexdir = str_replace(DIRECTORY_SEPARATOR.$dir,'',INDEXDIR);
        $dir = '';
    }else{
        $webdir = '';
        $indexdir = '';
        $dir = basename(__DIR__);
    }
    define("URLREL",str_replace('\\','/',str_replace($webdir,'',$indexdir)));
    define("URLRELSTATIC",URLREL?URLREL.'/'.basename(__DIR__):URLREL);
    
    
//	die(var_dump([$webdir,$indexdir,str_replace('\\','/',str_replace([$webdir.DIRECTORY_SEPARATOR,$webdir],'',$indexdir))]));
//	die(URLRELSTATIC);

//	trigger_error("This event WILL fire", E_USER_NOTICE);
    /**
     * Include services
     */
    require __DIR__ . '/..'.(VERSION?'/'.VERSION:'').'/app/config/services.php';//die(var_dump(__DIR__ . '/..'.(VERSION?'/'.VERSION:'').'/config/services.php'));

    /**
     * Include services
     */
    //require __DIR__ . '/../config/logger.php';

    /**
     * Handle the request
     */
    $application = new Application($di);

    /**
     * Include modules
     */
    require __DIR__ . '/..'.(VERSION?'/'.VERSION:'').'/app/config/modules.php';

    echo $application->handle()->getContent();
} catch (\Phalcon\Mvc\Dispatcher\Exception $e) {
    //die('Dispatcher');
    header("HTTP/1.0 404 Not Found");
    echo '<html><head></head><body><strong>' . $e->getMessage() . '</strong>, try to <a href="javascript:location.reload()">reload</a> or <a href="javascript:history.back()">go back</a></body></body></html>';
    //header('Content-type: application/json; charset=utf-8');
    //header("HTTP/1.0 404 Not Found");
    //echo json_encode(['responseCode' => 404, 'responseCodeDescription' => 'Not Found']);
    BilnaLogger::logme(null, $e->getMessage());
} catch (\Phalcon\Mvc\Application\Exception $e) {
    //die('Dispatcher');
    header("HTTP/1.0 404 Not Found");
    echo '<html><head></head><body><strong>' . $e->getMessage() . '</strong>, try to <a href="javascript:location.reload()">reload</a> or <a href="javascript:history.back()">go back</a></body></body></html>';
    //header('Content-type: application/json; charset=utf-8');
    //header("HTTP/1.0 404 Not Found");
    //echo json_encode(['responseCode' => 404, 'responseCodeDescription' => 'Not Found']);
    BilnaLogger::logme(null, $e->getMessage());
} catch (Exception $e) {
    //die('NOK');
    //die($_SERVER['REQUEST_URI']);
    if ((isset($_SERVER["Content-Type"]) && $_SERVER["Content-Type"]=='application/json')/* || (isset($_SERVER["Accept"]) && $_SERVER["Accept"]=='application/json')*/) {
        header("HTTP/1.0 500 Internal Server Error");
        header('Content-type: application/json; charset=utf-8');
        echo json_encode(['responseCode' => 901, 'responseCodeDescription' => $e->getMessage()]);
    } else {
        header("HTTP/1.0 500 Internal Server Error");
        echo '<html><head></head><body><strong>' . $e->getMessage() . '</strong>, try to <a href="javascript:location.reload()">reload</a> or <a href="javascript:history.back()">go back</a></body></body></html>';
    }
    //print_r($e->getMessage());
    BilnaLogger::logme(null, $e->getMessage());
}
die();