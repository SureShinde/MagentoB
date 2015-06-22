<?php

/**
 * Register application modules
 */
$application->registerModules(array(
	'customer' => array(
		'className' => 'Frontend\Customer\Module',
		'path' => __DIR__ . '/../modules/customer/Module.php'
	),
));
