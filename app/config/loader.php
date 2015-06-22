<?php
/**
 * User: Akbar
 * Date: 6/3/2015
 * Time: 10:36 AM
 */
$loader = new Phalcon\Loader;
if(substr(Phalcon\Version::get(),0,1)>=2){
	$namespaces = [
		'Frontend\Compability' => __DIR__ . '/../libraries/compability/2/',
	];
}else{
	$namespaces = [
		'Frontend\Compability' => __DIR__ . '/../libraries/compability/1/',
	];

}

$namespaces = array_merge($namespaces,[
    'Bilna\Phalcon' => __DIR__ . '/../libraries/Phalcon/',
    'Bilna\Libraries' => __DIR__ . '/../libraries/Bilna/',
    'Frontend\Controllers' => __DIR__ . '/../controllers/',
    'Frontend\Models' => __DIR__ . '/../models/',
    'Frontend\Widgets' => __DIR__ . '/../libraries/Bilna/Widgets/',
    'Frontend\Libraries' => __DIR__ . '/../libraries/',
]);
$loader->registerNamespaces($namespaces)->register();